# Codebase Summary

## Directory Structure

```
larajs-query/
├── src/
│   ├── LaraJSQuery.php               # Entry trait, delegates to QueryParser
│   ├── DTO/
│   │   ├── QueryParserRequestDTO.php # Request data structure
│   │   └── QueryParserAllowDTO.php   # Field whitelist structure
│   ├── QueryParser/
│   │   ├── QueryParserInterface.php  # Main contract
│   │   ├── QueryParser.php           # Orchestrator (168 LOC)
│   │   ├── FilterParser.php          # Filter→Eloquent converter (168 LOC)
│   │   ├── SortParser.php            # Sort→orderBy converter
│   │   ├── SearchParser.php          # Search→whereLike converter
│   │   ├── IncludeParser.php         # Include→with converter
│   │   ├── SelectParser.php          # Select→select converter
│   │   └── DateParser.php            # Date→whereBetween converter
│   ├── RequestParser/
│   │   ├── RequestParser.php         # Orchestrates sub-parsers (161 LOC)
│   │   ├── FilterParser.php          # Parse IBM filter syntax (292 LOC)
│   │   ├── SortParser.php            # Parse sort string
│   │   ├── SearchParser.php          # Parse search array
│   │   ├── IncludeParser.php         # Parse include array
│   │   ├── SelectParser.php          # Parse select string
│   │   └── DateParser.php            # Parse date array
│   ├── Enum/
│   │   ├── IbmOperator.php           # Functional operators (equals, greaterThan, etc.)
│   │   ├── SqlOperator.php           # SQL operators (=, >, <, LIKE, IN, BETWEEN)
│   │   ├── Operator.php              # Maps IBM→SQL operators
│   │   ├── Method.php                # Eloquent method names (where, whereIn, etc.)
│   │   ├── FilterStyle.php           # IBM vs MongoDB styles
│   │   └── IbmValueType.php          # Value type detection
│   ├── Repositories/
│   │   ├── BaseRepository.php        # Abstract base (110 LOC)
│   │   ├── ReadRepository.php        # Read-only ops (76 LOC)
│   │   ├── WriteRepository.php       # Create/update/delete (59 LOC)
│   │   ├── ReadRepositoryInterface.php
│   │   └── WriteRepositoryInterface.php
│   └── Providers/
│       └── LaraJSQueryServiceProvider.php  # Service provider (239 LOC)
├── config/
│   └── larajs-query.php              # Package config
├── tests/
│   ├── Providers/
│   ├── QueryParser/
│   └── RequestParser/
├── composer.json
└── README.md
```

**Total Source LOC: ~1872**

## File Descriptions

### Entry Point

| File | Lines | Purpose |
|------|-------|---------|
| `src/LaraJSQuery.php` | 23 | Trait for models; calls `app(QueryParserInterface::class)->parse()` |

### Data Transfer Objects (DTO)

| File | Lines | Purpose |
|------|-------|---------|
| `QueryParserRequestDTO` | ~50 | Wraps request query params: filter, sort, search, include, select, date, pagination |
| `QueryParserAllowDTO` | ~50 | Whitelist structure: field, include, sort, filter, search, date arrays |

### Query Parsing Pipeline

**RequestParser/** — Parses HTTP query strings into internal structures

| File | Lines | Purpose |
|------|-------|---------|
| `RequestParser.php` | 161 | Orchestrates: FilterParser, SortParser, SearchParser, IncludeParser, SelectParser, DateParser |
| `FilterParser.php` | 292 | Recursive descent parser for IBM filter syntax: `equals(name,'Smith')`, `and(...)`, `or(...)`, `not(...)` |
| `SortParser.php` | ~60 | Parses comma-separated sort string: `id,-updated_at,roles.name` → arrays |
| `SearchParser.php` | ~50 | Parses search array: `[column=>..., value=>...]` |
| `IncludeParser.php` | ~70 | Parses include array: `roles`, `roles.permissions`, `roles\|count`, `roles\|filter(...)` |
| `SelectParser.php` | ~40 | Parses select string: `id,name,description` |
| `DateParser.php` | ~60 | Parses date array: `[column=>..., value=>[start, end]]` |

**QueryParser/** — Converts parsed structures to Eloquent query calls

| File | Lines | Purpose |
|------|-------|---------|
| `QueryParser.php` | 168 | Orchestrates QueryParser sub-parsers; calls `Builder::where()`, `orderBy()`, `with()`, `select()`, `paginate()` |
| `FilterParser.php` | 168 | Maps filters to `Builder::where()`, `whereIn()`, `whereBetween()`, `whereHas()`, `whereRelationIn()` (custom macro) |
| `SortParser.php` | ~70 | Applies `orderBy()` and complex relationship sorting via BelongsToThrough |
| `SearchParser.py` | ~60 | Applies `whereLikeRelationship()` macro for LIKE queries |
| `IncludeParser.php` | ~120 | Applies `with()` with constraints, aggregates, and filtering |
| `SelectParser.php` | ~50 | Applies `select()` for column projection |
| `DateParser.php` | ~60 | Applies `whereBetween()` with auto-calculated boundaries |

### Enumerations

| File | Purpose |
|------|---------|
| `IbmOperator.php` | 15+ operators: equals, lessThan, greaterThan, contains, startsWith, endsWith, between, has, any, relation, not, and, or, exists, null |
| `SqlOperator.php` | SQL equivalents: =, <>, <, <=, >, >=, LIKE, IN, NOT IN, BETWEEN, IS NULL, IS NOT NULL |
| `Operator.php` | Maps IbmOperator → SqlOperator |
| `Method.php` | Eloquent method names: where, whereIn, whereBetween, whereHas, whereNull, etc. |
| `FilterStyle.php` | Enum: IBM, MongoDB (for future extension) |
| `IbmValueType.php` | Value type detection: String, Number, Boolean, Null |

### Repositories

| File | Lines | Purpose |
|------|-------|---------|
| `BaseRepository.php` | 110 | Abstract base implementing both ReadRepositoryInterface & WriteRepositoryInterface |
| `ReadRepository.php` | 76 | Extends BaseRepository; implements find(), findAll(), findOrFail(), laraJSQuery() |
| `WriteRepository.php` | 59 | Implements create(), update(), delete() |
| `ReadRepositoryInterface.php` | ~25 | Contract: findAll(), find(), findOrFail(), query(), laraJSQuery() |
| `WriteRepositoryInterface.php` | ~20 | Contract: create(), update(), delete() |

### Service Provider & Config

| File | Lines | Purpose |
|------|-------|---------|
| `LaraJSQueryServiceProvider.php` | 239 | Registers QueryParser singleton; adds 6 Builder macros: whereRelationIn, whereRelationBetween, whereLikeRelationship, collectionPaginate, orderByRelationship, dynamicPaginate |
| `config/larajs-query.php` | ~20 | limit.default=25, limit.max=500 |

### Tests

Located in `tests/` with PHPUnit 10.3+:
- `tests/Providers/` — Service provider tests
- `tests/QueryParser/` — Query parser unit tests
- `tests/RequestParser/` — Request parser unit tests

## Data Flow

### 1. Request Arrives

```http
GET /api/users?filter=and(equals(role,'admin'),greaterThan(age,'25'))&sort=name&include[]=posts&select=id,name&pagination[limit]=10&pagination[page]=1
```

### 2. Controller Uses LaraJSQuery Trait

```php
class UserController {
    use LaraJSQuery;
    
    public function index(Request $request) {
        $allow = QueryParserAllowDTO::fromArray([
            'filter' => ['role', 'age'],
            'include' => ['posts'],
            'sort' => ['name'],
            'select' => ['id', 'name', 'email'],
        ]);
        
        return User::query()
            ->applyLaraJSQuery(
                QueryParserRequestDTO::fromArray($request->query()),
                $allow
            )
            ->get();
    }
}
```

### 3. Trait Delegates to QueryParser

`LaraJSQuery::applyLaraJSQuery()` → `app(QueryParserInterface::class)->parse()`

### 4. QueryParser Orchestrates

```
QueryParser::parse($builder, $options, $allow)
  ├─ RequestParser::parse($options) → Parsed request DTO
  ├─ FilterParser::parse($builder, filter) → $builder->where(...)->whereIn(...)
  ├─ SortParser::parse($builder, sort) → $builder->orderBy(...)
  ├─ IncludeParser::parse($builder, include) → $builder->with(...)
  ├─ SelectParser::parse($builder, select) → $builder->select(...)
  ├─ SearchParser::parse($builder, search) → $builder->whereLikeRelationship(...)
  └─ DateParser::parse($builder, date) → $builder->whereBetween(...)
  
  → Return modified $builder
```

### 5. Builder Methods Applied

The Builder accumulates method calls:
- `where(field, operator, value)` — Direct field filters
- `whereHas(relation, callback)` — Relationship existence checks
- `whereRelationIn(relation, column, values)` — Macro for IN on relations
- `orderBy(column, direction)` — Sorting
- `orderByRelationship()` — Macro for complex relationship sorting
- `with(relation, callback)` — Eager loading with constraints
- `select(columns)` — Field projection
- `whereLikeRelationship(columns, value)` — Macro for LIKE on relations
- `whereBetween(column, [start, end])` — Date range filtering
- `paginate(limit, page)` or `simplePaginate()` or `cursorPaginate()` — Pagination

### 6. Query Executes

```php
$builder->get()  // Returns Collection
$builder->paginate()  // Returns LengthAwarePaginator
```

## Key Design Patterns

| Pattern | Location | Purpose |
|---------|----------|---------|
| **Trait-Based Entry** | `LaraJSQuery.php` | Composition over inheritance for model integration |
| **Pipeline/Orchestrator** | `QueryParser.php`, `RequestParser.php` | Sequential sub-parser invocation |
| **Strategy (Enum-Driven)** | `IbmOperator.php`, `FilterStyle.php` | Switch on enum cases to determine behavior |
| **Recursive Descent** | `RequestParser/FilterParser.php` | Parse nested AND/OR/NOT conditions |
| **Macro (Builder Macros)** | `LaraJSQueryServiceProvider.php` | Extend Eloquent without inheritance |
| **Repository Pattern** | `Repositories/` | Decouple business logic from Eloquent |
| **DTO Pattern** | `QueryParserRequestDTO.php`, `QueryParserAllowDTO.php` | Type-safe request/allow structures |

## Code Style & Standards

- **PHP Version**: 8.3+ (enums, readonly properties, named arguments)
- **Namespacing**: `LaraJS\Query\` prefix for all classes
- **Naming**: PascalCase classes, camelCase methods, UPPER_CASE constants
- **Type Hints**: Full return type + parameter type hints
- **Error Handling**: Exceptions via Illuminate contracts; no silent failures
- **Testing**: PHPUnit 10.3+ with Mockery for mocking

## Key Algorithms

### IBM Filter Parser (Recursive Descent)

```
parse(string $filter): Expression
  if filter matches function call pattern:
    extract function name & arguments
    recursively parse arguments (nested calls)
    return Expression object with function & args
  else:
    return string/number/null literal value
```

### Filter to Query Builder Mapping

```
for each filter in expressions:
  if operator is logical (AND, OR, NOT):
    apply closure with recursive handling
  else if operator has Relation suffix:
    apply whereHas() with relation & condition
  else:
    apply direct where() with SqlOperator
```

### Relationship Sorting

Uses `staudenmeir/belongs-to-through` to handle chains:
- `Comment` → `Post` → `User` → `Country`
- Sort by `country.name` via BelongsToThrough::orderByThroughRelationship()`

## Performance Characteristics

| Operation | Complexity | Notes |
|-----------|-----------|-------|
| Filter parsing | O(n) | Single pass, recursive for nesting |
| Query building | O(m) | m = number of filter/sort/include clauses |
| Pagination | O(1) | Database handles offset/limit |
| Eager loading | O(r) | r = number of relations (N+1 prevented by `with()`) |

## Testing Coverage

Tests verify:
- Filter expression parsing (valid/invalid syntax)
- Filter application to Builder (SQL generation)
- Sort order application
- Relationship inclusion with aggregates
- Field selection
- Date range filtering
- Pagination types
- Allow-list enforcement
- Edge cases (null values, special characters, nested relations)

## Dependencies

| Package | Version | Used For |
|---------|---------|----------|
| `illuminate/support` | ^11.0 | Service container, Collection utilities |
| `illuminate/database` | ^11.0 | Eloquent Builder, Model base |
| `illuminate/pagination` | ^11.0 | Paginator classes |
| `staudenmeir/belongs-to-through` | ^2.17 | Complex relationship sorting |

## Configuration Points

File: `config/larajs-query.php`

```php
return [
    'limit' => [
        'default' => 25,  // Default page size
        'max'     => 500, // Maximum page size (prevents resource exhaustion)
    ],
];
```

Publish via: `php artisan vendor:publish --tag=larajs-query`

## Extension Points

1. **Custom Operators**: Add cases to `IbmOperator.php`, map in `Operator.php`
2. **Filter Styles**: Add to `FilterStyle.php`, implement in `RequestParser/FilterParser.php`
3. **Repository Methods**: Extend `ReadRepository` or `WriteRepository`
4. **Builder Macros**: Register in `LaraJSQueryServiceProvider.php`
5. **Validation Rules**: Extend `QueryParserAllowDTO` validation logic

## Known Issues & Limitations

- Relationship sorting limited to models in BelongsToThrough chain
- Aggregates (`|count`) only on direct relationships, not nested
- Date filtering assumes datetime column; behavior undefined for strings
- Filter recursion depth unbounded (potential stack overflow on malicious input)
