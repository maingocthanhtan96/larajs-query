# System Architecture

## Overview

LaraJS Query uses a **pipeline architecture** to transform HTTP request query parameters into Eloquent query builder method calls. The design decouples request parsing from query building, enabling independent testing and future style extensions (e.g., MongoDB filter syntax).

## Request Lifecycle

```
1. HTTP Request                                   2. Parse Request Parameters
   GET /api/users                                    QueryParserRequestDTO
   ?filter=and(equals(role,'admin'),age>'25')       ├─ filters: array
   &sort=name                                        ├─ sorts: array
   &include[]=posts                                  ├─ includes: array
   &select=id,name                                   ├─ selects: array
   &pagination[limit]=10                             ├─ searches: array
                                                     ├─ dates: array
                                                     └─ pagination: array
                                   ↓
                            3. Validate Allow-List
                            QueryParserAllowDTO
                            ├─ field: ['id', 'name', ...]
                            ├─ filter: ['role', 'age', ...]
                            ├─ sort: ['name', ...]
                            └─ include: ['posts', ...]
                                   ↓
                            4. Build Query
                            QueryParser Pipeline
                            ├─ FilterParser
                            ├─ SortParser
                            ├─ IncludeParser
                            ├─ SelectParser
                            ├─ SearchParser
                            └─ DateParser
                                   ↓
                            5. Execute Query
                            Eloquent Builder
                            SELECT * FROM users
                            WHERE role = 'admin' AND age > 25
                            ORDER BY name
                            WITH posts
                                   ↓
                            6. Return Response
                            Collection / Paginator
```

## Architecture Layers

```
┌─────────────────────────────────────────────────────┐
│  Controller / Repository Layer                      │
│  (LaraJSQuery trait entry point)                   │
└────────────────┬────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────┐
│  Pipeline Orchestrator Layer                        │
│  (QueryParser & RequestParser)                      │
├─────────────────────────────────────────────────────┤
│  Request Parsing       │  Query Building            │
│  ─────────────────     │  ─────────────             │
│  ├─ FilterParser       │  ├─ FilterParser           │
│  ├─ SortParser         │  ├─ SortParser             │
│  ├─ IncludeParser      │  ├─ IncludeParser          │
│  ├─ SelectParser       │  ├─ SelectParser           │
│  ├─ SearchParser       │  ├─ SearchParser           │
│  └─ DateParser         │  └─ DateParser             │
└────────────────┬────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────┐
│  Data Transfer Objects (DTOs)                       │
│  ├─ QueryParserRequestDTO (parsed request)         │
│  └─ QueryParserAllowDTO (whitelist)                │
└────────────────┬────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────┐
│  Enumeration & Contract Layer                       │
│  ├─ IbmOperator (functional operators)             │
│  ├─ SqlOperator (SQL operators)                    │
│  ├─ Operator (IBM→SQL mapping)                     │
│  ├─ Method (Eloquent method names)                 │
│  └─ FilterStyle (parsing style)                    │
└────────────────┬────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────┐
│  Eloquent Builder Integration                       │
│  (Standard Eloquent methods + custom macros)       │
│  ├─ where(), whereIn(), whereBetween(), whereHas() │
│  ├─ orderBy(), with(), select(), paginate()       │
│  └─ Custom: whereRelationIn(), whereRelationBetween()
└────────────────┬────────────────────────────────────┘
                 │
                 ↓
         Database Query
```

## Component Interactions

### 1. Request Entry Point

**File**: `src/LaraJSQuery.php`

```php
trait LaraJSQuery
{
    public function applyLaraJSQuery(
        Builder $queryBuilder,
        QueryParserRequestDTO $options,
        QueryParserAllowDTO $allow
    ): Builder
    {
        // Resolve QueryParser from container (singleton)
        return app(QueryParserInterface::class)->parse($queryBuilder, $options, $allow);
    }
}
```

**Usage**:
```php
class UserController
{
    use LaraJSQuery;

    public function index(Request $request)
    {
        $users = User::query()
            ->applyLaraJSQuery(
                QueryParserRequestDTO::fromArray($request->query()),
                QueryParserAllowDTO::fromArray([...])
            )
            ->get();

        return $users;
    }
}
```

### 2. Request Parsing Pipeline

**File**: `src/RequestParser/RequestParser.php` (161 LOC)

```php
class RequestParser
{
    public function parse(
        QueryParserRequestDTO $options,
        QueryParserAllowDTO $allow
    ): ParsedRequest {
        // Returns ParsedRequest with normalized arrays:
        // - filters: [filter_expression_1, filter_expression_2, ...]
        // - sorts: [[column => 'name', direction => 'asc'], ...]
        // - includes: [[relation => 'posts', aggregate => null], ...]
        // - etc.
    }
}
```

**Sub-parsers**:
- `RequestParser/FilterParser.php` — Parses IBM filter syntax recursively
- `RequestParser/SortParser.php` — Parses sort string (comma-separated)
- `RequestParser/SearchParser.php` — Parses search array
- `RequestParser/IncludeParser.php` — Parses include array with aggregates/filters
- `RequestParser/SelectParser.php` — Parses select string
- `RequestParser/DateParser.php` — Parses date array

**Flow**:
```
Raw HTTP query array
  ↓
RequestParser::parse()
  ├─ FilterParser::parse('filter=equals(name,"Smith")')
  │  → Expression tree: {func: 'equals', args: ['name', 'Smith']}
  ├─ SortParser::parse('sort=name,-age')
  │  → [{field: 'name', asc}, {field: 'age', desc}]
  ├─ SearchParser::parse('search[column]=name&search[value]=test')
  │  → [{columns: ['name'], value: 'test'}]
  ├─ IncludeParser::parse('include[]=posts&include[]=posts|count')
  │  → [{relation: 'posts', aggregate: null}, {relation: 'posts', aggregate: 'count'}]
  ├─ SelectParser::parse('select=id,name,email')
  │  → ['id', 'name', 'email']
  ├─ DateParser::parse('date[column]=created_at&date[value]=[2025-01-01, 2025-12-31]')
  │  → [{column: 'created_at', start: '2025-01-01', end: '2025-12-31'}]
  └─ Validate against QueryParserAllowDTO allow-list
     → Return ParsedRequest
```

### 3. Query Building Pipeline

**File**: `src/QueryParser/QueryParser.php` (168 LOC)

```php
class QueryParser implements QueryParserInterface
{
    public function parse(
        Builder $builder,
        QueryParserRequestDTO $options,
        QueryParserAllowDTO $allow
    ): Builder {
        // Step 1: Parse request into structured format
        $request = $this->requestParser->parse($options, $allow);

        // Step 2: Apply each parser sequentially
        $builder = $this->filterParser->parse($builder, $request->filters, $allow);
        $builder = $this->sortParser->parse($builder, $request->sorts, $allow);
        $builder = $this->includeParser->parse($builder, $request->includes, $allow);
        $builder = $this->selectParser->parse($builder, $request->selects, $allow);
        $builder = $this->searchParser->parse($builder, $request->searches, $allow);
        $builder = $this->dateParser->parse($builder, $request->dates, $allow);

        // Step 3: Apply pagination
        return $this->paginationParser->parse($builder, $request->pagination, $allow);
    }
}
```

**Sub-parsers apply these Eloquent methods**:

| Parser | Eloquent Method | Example |
|--------|-----------------|---------|
| FilterParser | `where()`, `whereIn()`, `whereBetween()`, `whereHas()` | `$builder->where('role', '=', 'admin')` |
| SortParser | `orderBy()`, `orderByRelationship()` | `$builder->orderBy('name', 'asc')` |
| IncludeParser | `with()`, `withCount()`, `withExists()` | `$builder->with('posts')` |
| SelectParser | `select()` | `$builder->select('id', 'name')` |
| SearchParser | `whereLikeRelationship()` | `$builder->whereLikeRelationship('name', 'john')` |
| DateParser | `whereBetween()` | `$builder->whereBetween('created_at', [$start, $end])` |

### 4. Filter Parsing (Deep Dive)

**File**: `src/RequestParser/FilterParser.php` (292 LOC)

Implements recursive descent parser for IBM filter syntax:

```
Filter Expression Grammar:
─────────────────────────

expression := function_call | literal

function_call := IDENTIFIER '(' arguments ')'

arguments := expression (',' expression)*

literal := STRING | NUMBER | NULL | BOOLEAN

Examples:
─────────
equals(name, 'Smith')
greaterThan(age, '25')
and(equals(role, 'admin'), greaterThan(age, '25'))
or(has(posts, '1'), has(invoices, '1'))
relation(users, equals(name, 'Smith'))
not(equals(deleted_at, null))
```

**Algorithm**:

```
parse(expression: string) → Expression
  1. If expression matches function call pattern:
     a. Extract function name
     b. Extract argument strings
     c. Recursively parse each argument (handles nesting)
     d. Return Expression(function, parsed_args)

  2. Else if expression matches string literal:
     a. Extract quoted string
     b. Return StringLiteral

  3. Else if expression is numeric:
     a. Return NumericLiteral

  4. Else if expression is 'null':
     a. Return NullLiteral

  5. Else:
     a. Throw InvalidFilterExpression
```

**Example Trace**:

Input: `and(equals(name,'Smith'),greaterThan(age,'25'))`

```
parse("and(equals(name,'Smith'),greaterThan(age,'25'))")
  ├─ Recognize: function call 'and'
  ├─ Extract arguments: ["equals(name,'Smith')", "greaterThan(age,'25')"]
  ├─ Recursively parse arg 1:
  │  parse("equals(name,'Smith')")
  │    ├─ Recognize: function call 'equals'
  │    ├─ Extract arguments: ["name", "'Smith'"]
  │    ├─ Recursively parse arg 1: parse("name") → FieldName("name")
  │    ├─ Recursively parse arg 2: parse("'Smith'") → StringLiteral("Smith")
  │    └─ Return Expression('equals', [FieldName("name"), StringLiteral("Smith")])
  ├─ Recursively parse arg 2:
  │  parse("greaterThan(age,'25')")
  │    └─ Return Expression('greaterThan', [FieldName("age"), StringLiteral("25")])
  └─ Return Expression('and', [Expression(...), Expression(...)])
```

### 5. Filter Application (Query Building)

**File**: `src/QueryParser/FilterParser.php` (168 LOC)

Converts parsed filter expressions to Eloquent builder calls:

```php
private function applyFilter(Builder $builder, Expression $expression, QueryParserAllowDTO $allow): Builder
{
    $operator = $expression->function; // e.g., 'equals', 'greaterThan', 'and'
    $args = $expression->args;

    return match ($operator) {
        // Simple comparisons
        'equals' => $this->applySimple($builder, $args, IbmOperator::EQUALS, $allow),
        'greaterThan' => $this->applySimple($builder, $args, IbmOperator::GREATER_THAN, $allow),
        'contains' => $this->applySimple($builder, $args, IbmOperator::CONTAINS, $allow),

        // Logical operators
        'and' => $this->applyLogical($builder, $args, 'and', $allow),
        'or' => $this->applyLogical($builder, $args, 'or', $allow),
        'not' => $this->applyNegation($builder, $args[0], $allow),

        // Relationship filters
        'has' => $this->applyHas($builder, $args, $allow),
        'relation' => $this->applyRelation($builder, $args, $allow),
        'equalsRelation' => $this->applyRelationComparison($builder, $args, IbmOperator::EQUALS, $allow),

        default => throw new InvalidOperator($operator),
    };
}
```

**Mapping Examples**:

| IBM Operator | SQL Operator | Eloquent Method |
|--------------|--------------|-----------------|
| `equals(name, 'Smith')` | `=` | `where('name', '=', 'Smith')` |
| `greaterThan(age, '25')` | `>` | `where('age', '>', 25)` |
| `contains(desc, 'hello')` | `LIKE` | `where('desc', 'LIKE', '%hello%')` |
| `between(price, '10', '20')` | `BETWEEN` | `whereBetween('price', [10, 20])` |
| `any(status, 'active', 'pending')` | `IN` | `whereIn('status', ['active', 'pending'])` |
| `has(posts, '1')` | `EXISTS` | `has('posts')` where count >= 1 |
| `equalsRelation(author, name, 'Smith')` | `INNER JOIN` + `=` | `whereHas('author', fn($q) => $q->where('name', '=', 'Smith'))` |

### 6. Builder Macros

**File**: `src/Providers/LaraJSQueryServiceProvider.php` (239 LOC)

Extends Eloquent Builder with custom methods:

| Macro | Purpose | Example |
|-------|---------|---------|
| `whereRelationIn()` | Filter by relation column in array | `whereRelationIn('roles', 'id', [1, 2, 3])` |
| `whereRelationBetween()` | Filter by relation column range | `whereRelationBetween('posts', 'views', [100, 1000])` |
| `whereLikeRelationship()` | LIKE search on relations | `whereLikeRelationship(['name', 'posts.title'], 'search_term')` |
| `orderByRelationship()` | Sort by related column | `orderByRelationship('author', 'name')` |
| `collectionPaginate()` | Paginate in-memory collection | `$collection->paginate(15)` |
| `dynamicPaginate()` | Intelligently choose paginator type | `dynamicPaginate($limit, $page)` |

**Implementation Example**:

```php
Builder::macro('whereRelationIn', function ($relation, $column, $values) {
    return $this->whereHas($relation, function ($query) use ($column, $values) {
        $query->whereIn($column, $values);
    });
});
```

### 7. Relationship Sorting

Uses `staudenmeir/belongs-to-through` for complex chains:

```
Comment → Post → User → Country
  ↓         ↓      ↓       ↓
  id        id     id      id
  post_id   user_id country_id

Query: Sort comments by country.name
SELECT comments.* FROM comments
INNER JOIN posts ON comments.post_id = posts.id
INNER JOIN users ON posts.user_id = users.id
INNER JOIN countries ON users.country_id = countries.id
ORDER BY countries.name ASC
```

## Data Transfer Objects

### QueryParserRequestDTO

```php
final readonly class QueryParserRequestDTO
{
    public function __construct(
        public array $filters = [],      // Filter expressions
        public array $sorts = [],        // Sort directives
        public array $includes = [],     // Relationship includes
        public array $selects = [],      // Column selections
        public array $searches = [],     // Search terms
        public array $dates = [],        // Date range filters
        public array $pagination = [],   // Pagination settings
    ) {}
}
```

### QueryParserAllowDTO

```php
final readonly class QueryParserAllowDTO
{
    public function __construct(
        public array $field = [],        // Whitelist: columns for SELECT
        public array $include = [],      // Whitelist: relations for eager loading
        public array $sort = [],         // Whitelist: columns for ORDER BY
        public array $filter = [],       // Whitelist: columns for filtering
        public array $search = [],       // Whitelist: columns for LIKE search
        public array $date = [],         // Whitelist: columns for date filtering
    ) {}
}
```

## Enumerations

### IbmOperator

Defines 15+ functional operators:

```php
enum IbmOperator: string
{
    case EQUALS = 'equals';
    case LESS_THAN = 'lessThan';
    case GREATER_THAN = 'greaterThan';
    case CONTAINS = 'contains';
    case STARTS_WITH = 'startsWith';
    case ENDS_WITH = 'endsWith';
    case ANY = 'any';
    case BETWEEN = 'between';
    case HAS = 'has';
    case RELATION = 'relation';
    case NOT = 'not';
    case AND = 'and';
    case OR = 'or';
    // ... relationship variants: equalsRelation, greaterThanRelation, etc.
}
```

### Operator Mapping

Maps IBM operators to SQL operators:

```php
enum Operator
{
    case EQUALS maps to SqlOperator::EQUAL;
    case GREATER_THAN maps to SqlOperator::GREATER_THAN;
    case CONTAINS maps to SqlOperator::LIKE;
    case BETWEEN maps to SqlOperator::BETWEEN;
    // ...
}
```

## Dependency Injection

**Service Container Registration** (LaraJSQueryServiceProvider):

```php
$this->app->singleton(QueryParserInterface::class, function (Application $app) {
    return new QueryParser(
        $app->make(RequestParser::class),
        $app->make(FilterParser::class),      // QueryParser version
        $app->make(SortParser::class),
        $app->make(IncludeParser::class),
        $app->make(SelectParser::class),
        $app->make(SearchParser::class),
        $app->make(DateParser::class),
    );
});
```

All sub-parsers automatically resolve from container.

## Error Handling

| Exception | Thrown By | Reason |
|-----------|-----------|--------|
| `InvalidFilterExpression` | RequestParser/FilterParser | Malformed filter syntax |
| `InvalidOperator` | QueryParser/FilterParser | Unknown IBM operator |
| `UnallowedField` | QueryParserAllowDTO validation | Field not in whitelist |
| `InvalidRelation` | IncludeParser | Relation does not exist on model |
| `InvalidDateRange` | DateParser | Start > End or invalid format |

## Extension Points

### Adding Custom Operators

1. Add case to `IbmOperator` enum
2. Add mapping in `Operator::map()`
3. Implement in `RequestParser/FilterParser::parse()`
4. Implement in `QueryParser/FilterParser::applyFilter()`
5. Test both parsers

### Adding Custom Parser

1. Create `RequestParser/{Feature}Parser.php`
2. Create `QueryParser/{Feature}Parser.php`
3. Register in `LaraJSQueryServiceProvider`
4. Add field to DTOs
5. Invoke from main orchestrators

### Custom Repository Methods

Extend `ReadRepository` or `WriteRepository`:

```php
class UserRepository extends ReadRepository
{
    public function findAdminsByQuery(QueryParserAllowDTO $allow)
    {
        return $this->applyLaraJSQuery(
            $this->model::query()->where('role', 'admin'),
            QueryParserRequestDTO::fromArray(request()->query()),
            $allow
        )->paginate();
    }
}
```

## Performance Considerations

| Operation | Complexity | Notes |
|-----------|-----------|-------|
| Filter parsing | O(n) | Single recursive pass through expression |
| Query building | O(m) | m = number of clauses (filter, sort, include) |
| Pagination | O(1) | Database handles offset/limit |
| Eager loading | O(r+q) | r = relations, q = queries (prevents N+1 via `with()`) |
| Relationship sorting | O(j) | j = number of joins (BelongsToThrough creates joins) |

## Configuration

**File**: `config/larajs-query.php`

```php
return [
    'limit' => [
        'default' => 25,   // Default pagination size
        'max'     => 500,  // Maximum allowed limit (prevents resource exhaustion)
    ],
];
```

Published via: `php artisan vendor:publish --tag=larajs-query`

## Testing Strategy

| Layer | Test Type | Location |
|-------|-----------|----------|
| Request parsing | Unit | `tests/RequestParser/` |
| Query building | Unit | `tests/QueryParser/` |
| Eloquent integration | Integration | `tests/Providers/` |
| Repository pattern | Integration | `tests/Repositories/` |

All tests use Laravel's testing utilities and Mockery for mocks.
