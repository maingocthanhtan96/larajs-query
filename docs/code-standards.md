# Code Standards & Codebase Structure

## PHP Version & Features

**Minimum PHP**: 8.3+

### Language Features Used

| Feature | Example | Why |
|---------|---------|-----|
| **Enums** | `enum IbmOperator: string` | Type-safe operator definitions, prevents invalid values |
| **Readonly Properties** | `readonly Model $model` | Immutable DTOs, prevents accidental mutations |
| **Named Arguments** | `parse(builder: $b, options: $opts)` | Self-documenting code, parameter clarity |
| **Match Expressions** | `match($style) { FilterStyle::IBM => ... }` | Type-safe switch replacement |
| **Union Types** | `Builder\|Collection` | Explicit return type flexibility |
| **Nullsafe Operator** | `$relation?->getModel()` | Safe property access chains |
| **Constructor Property Promotion** | `public function __construct(private readonly Model $model)` | Concise dependency injection |

## Directory Structure

```
src/
├── LaraJSQuery.php                    # Entry trait
├── DTO/                               # Data structures
│   ├── QueryParserRequestDTO.php
│   └── QueryParserAllowDTO.php
├── QueryParser/                       # Query building (Builder methods)
│   ├── QueryParserInterface.php       # Contract
│   ├── QueryParser.php                # Orchestrator
│   ├── FilterParser.php
│   ├── SortParser.php
│   ├── SearchParser.php
│   ├── IncludeParser.php
│   ├── SelectParser.php
│   └── DateParser.php
├── RequestParser/                     # Request parsing (HTTP→internal)
│   ├── RequestParser.php              # Orchestrator
│   ├── FilterParser.php
│   ├── SortParser.php
│   ├── SearchParser.php
│   ├── IncludeParser.php
│   ├── SelectParser.php
│   └── DateParser.php
├── Enum/                              # Enumerations
│   ├── IbmOperator.php
│   ├── SqlOperator.php
│   ├── Operator.php
│   ├── Method.php
│   ├── FilterStyle.php
│   └── IbmValueType.php
├── Repositories/                      # Business logic layer
│   ├── BaseRepository.php
│   ├── ReadRepository.php
│   ├── WriteRepository.php
│   ├── ReadRepositoryInterface.php
│   └── WriteRepositoryInterface.php
└── Providers/
    └── LaraJSQueryServiceProvider.php
```

## Naming Conventions

### Classes & Interfaces

- **Case**: PascalCase
- **Suffix**: `Interface` for contracts, `Repository` for data access, `Parser` for parsing logic
- **Examples**: `QueryParserInterface`, `ReadRepository`, `FilterParser`

### Methods

- **Case**: camelCase
- **Naming**: Action verb + noun (parse, apply, filter, build, resolve)
- **Examples**: `applyLaraJSQuery()`, `parseFilter()`, `buildWhere()`

### Properties

- **Case**: camelCase (public), `$` prefix (variables)
- **Readonly**: Use `readonly` modifier for immutable dependencies
- **Examples**: `private readonly Model $model`, `protected string $filterExpression`

### Constants & Enums

- **Case**: UPPER_SNAKE_CASE (constants), PascalCase (enum names), UPPER_CASE (enum cases)
- **Examples**: `const DEFAULT_LIMIT = 25`, `enum IbmOperator`, `equals`, `greaterThan`

### Namespacing

All classes use `LaraJS\Query\` prefix:
```php
namespace LaraJS\Query\QueryParser;
namespace LaraJS\Query\Repositories;
namespace LaraJS\Query\Enum;
```

## Code Organization Patterns

### 1. Trait-Based Entry Point

**File**: `src/LaraJSQuery.php`

```php
trait LaraJSQuery
{
    public function applyLaraJSQuery(
        Builder $queryBuilder,
        QueryParserRequestDTO $options,
        QueryParserAllowDTO $allow
    ): Builder {
        return app(QueryParserInterface::class)->parse($queryBuilder, $options, $allow);
    }
}
```

**Why**: Composition allows models to use LaraJSQuery without inheritance chain changes.

### 2. Pipeline/Orchestrator Pattern

**File**: `src/QueryParser/QueryParser.php`

```php
class QueryParser implements QueryParserInterface
{
    public function __construct(
        private readonly RequestParser $requestParser,
        private readonly FilterParser $filterParser,
        // ... other parsers
    ) {}

    public function parse(Builder $builder, QueryParserRequestDTO $options, QueryParserAllowDTO $allow): Builder
    {
        $request = $this->requestParser->parse($options, $allow);
        $builder = $this->filterParser->parse($builder, $request->filters, $allow);
        $builder = $this->sortParser->parse($builder, $request->sorts, $allow);
        $builder = $this->includeParser->parse($builder, $request->includes, $allow);
        // ... continue for other parsers
        return $builder;
    }
}
```

**Why**: Sequential sub-parser invocation keeps orchestration logic separate from details.

### 3. Enum-Driven Strategy

**File**: `src/Enum/IbmOperator.php`

```php
enum IbmOperator: string
{
    case EQUALS = 'equals';
    case GREATER_THAN = 'greaterThan';
    case CONTAINS = 'contains';
    // ...
}
```

**Usage**:
```php
match($operator) {
    IbmOperator::EQUALS => $builder->where($field, '=', $value),
    IbmOperator::GREATER_THAN => $builder->where($field, '>', $value),
    default => throw new InvalidOperator()
};
```

**Why**: Type-safe, prevents typos, enables IDE autocomplete, avoids string-based conditionals.

### 4. DTO Pattern for Immutability

**File**: `src/DTO/QueryParserRequestDTO.php`

```php
final readonly class QueryParserRequestDTO
{
    public function __construct(
        public array $filters = [],
        public array $sorts = [],
        public array $includes = [],
        public array $selects = [],
        public array $searches = [],
        public array $dates = [],
        public array $pagination = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filters: $data['filter'] ?? [],
            sorts: $data['sort'] ?? [],
            // ...
        );
    }
}
```

**Why**: Type-safe request data, immutable state, clear contracts between layers.

### 5. Recursive Descent Parser

**File**: `src/RequestParser/FilterParser.php` (292 LOC)

```php
private function parse(string $expression): Expression
{
    // Match function call: functionName(arg1, arg2, ...)
    if (preg_match('/^(\w+)\((.*)\)$/', $expression, $matches)) {
        $function = $matches[1];
        $arguments = $this->parseArguments($matches[2]); // Recursive
        return new Expression($function, $arguments);
    }

    // Match string literal: 'value'
    if (preg_match('/^[\'"](.*)[\'\"]$/', $expression, $matches)) {
        return new StringLiteral($matches[1]);
    }

    // Match number
    if (is_numeric($expression)) {
        return new NumericLiteral((int) $expression);
    }

    // Match null
    if ($expression === 'null') {
        return new NullLiteral();
    }

    throw new InvalidFilterExpression($expression);
}
```

**Why**: Handles deeply nested filters: `and(equals(name,'X'), or(has(posts,'1'), greaterThan(age,'25')))`

### 6. Repository Pattern for Separation of Concerns

**File**: `src/Repositories/ReadRepository.php`

```php
final class ReadRepository implements ReadRepositoryInterface
{
    use LaraJSQuery;

    public function __construct(
        private readonly Model $model,
        private readonly int $limit,
        private readonly int $maxLimit,
    ) {}

    public function findAll(QueryParserAllowDTO $allow): LengthAwarePaginator|Collection
    {
        return $this->applyLaraJSQuery(
            $this->model::query(),
            QueryParserRequestDTO::fromArray(request()->query()),
            $allow
        )->paginate($this->limit);
    }

    public function find(int $id, QueryParserAllowDTO $allow)
    {
        return $this->applyLaraJSQuery(
            $this->model::query(),
            QueryParserRequestDTO::fromArray(request()->query()),
            $allow
        )->find($id);
    }
}
```

**Why**: Business logic layer is independent of HTTP/Eloquent details, testable in isolation.

### 7. Service Provider for Dependency Injection

**File**: `src/Providers/LaraJSQueryServiceProvider.php` (239 LOC)

```php
class LaraJSQueryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register singleton
        $this->app->singleton(QueryParserInterface::class, function (Application $app) {
            return new QueryParser(
                $app->make(RequestParser::class),
                $app->make(FilterParser::class),
                // ... other parsers
            );
        });

        // Register Builder macros
        $this->whereRelationIn();
        $this->whereRelationBetween();
        $this->whereLikeRelationship();
        // ...
    }

    private function whereRelationIn(): void
    {
        Builder::macro('whereRelationIn', function ($relation, $column, $values) {
            return $this->whereHas($relation, function ($query) use ($column, $values) {
                $query->whereIn($column, $values);
            });
        });
    }
}
```

**Why**: Centralized dependency setup, Builder macros available globally, Laravel integration point.

## Code Quality Standards

### Type Hints

**Required for**:
- All method parameters
- All method return types
- Property declarations (readonly/typed)
- DTO properties

```php
// Good
public function parse(
    Builder $builder,
    QueryParserRequestDTO $options,
    QueryParserAllowDTO $allow
): Builder {}

// Avoid
public function parse($builder, $options, $allow) {}
```

### Error Handling

- Throw exceptions for invalid input (not silent failures)
- Use Illuminate exceptions or custom exceptions
- Document exceptions in PHPDoc

```php
public function parse(string $filter): Expression
{
    // Validate before processing
    if (empty($filter)) {
        throw new InvalidFilterExpression('Filter cannot be empty');
    }

    // Process...
    if (!preg_match($pattern, $filter)) {
        throw new InvalidFilterExpression("Invalid filter syntax: $filter");
    }
}
```

### Method Length

- Keep methods ≤50 lines
- Extract logic into private helper methods
- Use `match()` instead of nested `if/else`

```php
// Extract into private method
private function applyFilterOperator(Builder $builder, string $operator, array $args): Builder
{
    return match ($operator) {
        'equals' => $builder->where($args[0], '=', $args[1]),
        'greaterThan' => $builder->where($args[0], '>', $args[1]),
        default => throw new InvalidOperator($operator),
    };
}
```

### Immutability & Side Effects

- DTOs are `readonly final`
- Avoid mutable state in parsers
- Return new Builder instances (Eloquent pattern)

```php
// Good: Returns new builder, no side effects
public function parse(Builder $builder, array $filters): Builder
{
    foreach ($filters as $filter) {
        $builder = $builder->where(...);
    }
    return $builder;
}

// Avoid: Modifying global state
public function parse(Builder $builder, array $filters): void
{
    foreach ($filters as $filter) {
        $builder->where(...); // Side effect
    }
}
```

### Comments & Documentation

- Use PHPDoc for public methods
- Explain "why" for complex logic, not "what"
- Keep comments up-to-date with code

```php
// Good: Explains the algorithm
/**
 * Parse IBM-style filter expressions using recursive descent.
 *
 * Handles nested AND/OR/NOT conditions and relationship filters.
 * Example: and(equals(name,'Smith'), greaterThan(age,'25'))
 *
 * @param string $expression Filter expression
 * @return Expression Parsed expression tree
 * @throws InvalidFilterExpression If syntax is invalid
 */
public function parse(string $expression): Expression {}

// Avoid: Obvious comments
/**
 * Check if filter is valid
 * @param $f Filter
 * @return bool
 */
public function isValid($f) {}
```

## Testing Standards

### Location

- Tests mirror source structure: `tests/RequestParser/`, `tests/QueryParser/`, etc.
- Use PHPUnit 10.3+
- Test file name = Class name + `Test` suffix

### Coverage Requirements

| Category | Min Coverage |
|----------|--------------|
| Core parsing logic | 85% |
| Builder integration | 75% |
| Error cases | 100% |
| Repositories | 70% |

### Test Organization

```php
class FilterParserTest extends TestCase
{
    // Test valid cases
    public function test_parse_equals_filter(): void
    {
        $result = $this->parser->parse("equals(name,'Smith')");
        $this->assertEquals('equals', $result->operator);
    }

    // Test edge cases
    public function test_parse_nested_and_condition(): void {}
    public function test_parse_null_value(): void {}

    // Test error cases
    public function test_parse_invalid_syntax_throws(): void
    {
        $this->expectException(InvalidFilterExpression::class);
        $this->parser->parse("invalid(");
    }
}
```

## Performance Guidelines

### Query Building

- Batch similar operations (multiple `where()` before execution)
- Use eager loading with `with()` to prevent N+1 queries
- Limit recursion depth in filter parsing (consider recursion limit)

### Pagination

- Respect configured max limit (default 500)
- Use cursor pagination for large datasets
- Index sortable columns in database

### Caching

- Do not cache parsed DTOs (query parameters change per request)
- Cache configuration (larajs-query.php) via Laravel config cache

## Security Standards

### SQL Injection Prevention

- All values bound as parameters (Eloquent handles)
- Validate operator names against `IbmOperator` enum
- Whitelist allowed fields in `QueryParserAllowDTO`

```php
// Good: Validates operator
if (!in_array($operator, IbmOperator::cases())) {
    throw new InvalidOperator($operator);
}

// Avoid: String concatenation
$query = "WHERE name = '$value'"; // SQL injection risk
```

### Authorization

- Filter operations respect allow-list in `QueryParserAllowDTO`
- Repository layer can further restrict based on user permissions
- Do NOT query fields without explicit allow-list entry

```php
$allow = QueryParserAllowDTO::fromArray([
    'filter' => ['public_field', 'email'], // Only these can be filtered
    'search' => ['name'],                  // Only these can be searched
    'include' => ['posts'],                // Only these can be included
]);
```

### Input Validation

- Empty filters/sorts/searches are handled gracefully
- Invalid enum values throw exceptions
- Date strings validated before use in `whereBetween()`

## Conventions for Adding New Features

### Adding a New Operator

1. Add case to `IbmOperator` enum
2. Add SQL mapping to `Operator` enum
3. Implement in `RequestParser/FilterParser` (recognition)
4. Implement in `QueryParser/FilterParser` (application)
5. Add unit tests for both parsers
6. Update README.md with operator documentation

### Adding a New Parser (e.g., for new query parameter)

1. Create `RequestParser/{Feature}Parser.php`
2. Create `QueryParser/{Feature}Parser.php`
3. Add method to `RequestParser::parse()` and `QueryParser::parse()`
4. Register in `LaraJSQueryServiceProvider` if needed
5. Add DTO field in `QueryParserRequestDTO`
6. Add allow-list configuration in `QueryParserAllowDTO`
7. Write comprehensive tests

### Modifying Existing Behavior

- Update related tests first (TDD)
- Maintain backward compatibility if possible
- Document breaking changes in CHANGELOG.md
- Update README.md and docs/

## Linting & Formatting

- Use Laravel Pint (^1.11) for code formatting
- Run before commit: `./vendor/bin/pint`
- No manual formatting needed

## Checklist for New Code

- [ ] Type hints on all parameters & return types
- [ ] PHPDoc on public methods
- [ ] Unit tests with ≥75% coverage
- [ ] Error cases tested and documented
- [ ] No console.log/dump/dd() left in code
- [ ] Enum values used instead of strings
- [ ] SQL injection prevented via parameter binding
- [ ] Allow-list respected for security
- [ ] Backward compatibility maintained or documented
- [ ] Related documentation updated
