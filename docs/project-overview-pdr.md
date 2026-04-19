# Project Overview & PDR

## Project Summary

**LaraJS Query** is a Laravel Eloquent query builder package (v2.0.0) that eliminates boilerplate API filtering logic. It dynamically applies filters, sorts, searches, includes relationships, selects fields, and paginates based on HTTP request query parameters using a functional filter syntax.

**Author:** tanmnt (maingocthanhtan96@gmail.com)
**License:** Open Source
**Repository:** larajs/query

## Target Audience

- Laravel API developers building REST/GraphQL endpoints
- Teams standardizing query parameter syntax across microservices
- Developers seeking reduced controller bloat for data retrieval

## Value Proposition

Reduces query-building boilerplate from 50+ lines of controller code to 2-3 method calls. Provides a standard, composable query syntax that clients can leverage without server-side changes.

## Key Features

| Feature | Description |
|---------|-------------|
| **Filtering** | IBM-style functional syntax (e.g., `equals(name,'Smith')`) with 15+ operators and relationship variants |
| **Sorting** | Multi-column, ascending/descending, relationship sorting via BelongsToThrough joins |
| **Searching** | LIKE-based, multi-column, relationship search |
| **Including** | Eager loading with nesting, aggregates (count/exists/sum/min/max/avg), filtered includes |
| **Field Selection** | Specify which columns to return via `select` parameter |
| **Date Filtering** | Auto-calculated startOfDay/endOfDay with whereBetween |
| **Pagination** | Default/simple/cursor pagination with configurable limits |
| **Repository Pattern** | Read/Write split interfaces with LaraJSQuery trait |
| **Security** | Allow-list whitelisting of queryable fields, sortable columns, searchable relations |

## Functional Requirements

| Requirement | Status |
|-------------|--------|
| Parse HTTP query strings into internal DTOs | Complete |
| Apply filters using IBM operator syntax | Complete |
| Support relationship filters | Complete |
| Multi-column sorting with nesting | Complete |
| Relationship searching via LIKE | Complete |
| Eager load with aggregates | Complete |
| Date range filtering | Complete |
| Pagination (3 types) | Complete |
| Field selection/projection | Complete |
| Allow-list configuration | Complete |
| Repository pattern interfaces | Complete |

## Non-Functional Requirements

| Requirement | Specification |
|-------------|----------------|
| **PHP Version** | PHP 8.3+ (enum, readonly, named args) |
| **Laravel Versions** | 11.0, 12.0, 13.0 |
| **Performance** | Query building should not exceed 5ms per request |
| **Code Coverage** | Unit tests in `tests/` directory, integrated with PHPUnit 10.3+ |
| **Code Quality** | Linting via Laravel Pint 1.11+, no hard-coding of operators |
| **Backward Compatibility** | Maintain v1.x API surface for existing consumers |

## Technical Constraints

1. **Dependency on BelongsToThrough**: Complex relationship sorting requires staudenmeir/belongs-to-through ^2.17
2. **Pagination Limit**: Configurable max limit (default 500) to prevent resource exhaustion
3. **Filter Parsing**: Recursive descent parser must handle deeply nested AND/OR/NOT conditions
4. **Database Abstraction**: Must work with Eloquent's query builder for PostgreSQL, MySQL, SQLite

## Architecture Pattern: Pipeline

```
HTTP Request
    ↓
LaraJSQuery::applyLaraJSQuery()
    ↓
QueryParser::parse()
    ↓
[RequestParser → Internal DTO]
    ↓
[FilterParser, SortParser, IncludeParser, SelectParser, SearchParser, DateParser]
    ↓
[Converts to Eloquent method calls: where(), join(), with(), select(), orderBy(), limit()]
    ↓
Builder→get()|paginate()|first()
    ↓
JSON Response
```

## Core Components

| Component | Location | Purpose |
|-----------|----------|---------|
| **LaraJSQuery Trait** | `src/LaraJSQuery.php` | Entry point, delegates to QueryParser |
| **QueryParser** | `src/QueryParser/QueryParser.php` | Orchestrates sub-parsers, applies to Builder |
| **RequestParser** | `src/RequestParser/` | Parses query strings into DTOs |
| **DTOs** | `src/DTO/` | QueryParserRequestDTO, QueryParserAllowDTO |
| **Enums** | `src/Enum/` | IbmOperator, SqlOperator, Method, FilterStyle |
| **Repositories** | `src/Repositories/` | BaseRepository, ReadRepository, WriteRepository |
| **Service Provider** | `src/Providers/LaraJSQueryServiceProvider.php` | Registers singleton, Builder macros |

## Configuration

File: `config/larajs-query.php`

```php
'limit' => [
    'default' => 25,
    'max'     => 500,
]
```

Published via `php artisan vendor:publish --tag=larajs-query`

## Security Model

- **Field Whitelisting**: QueryParserAllowDTO restricts queryable fields, sortable columns, searchable relations
- **Injection Prevention**: Filter parser validates operator names against IbmOperator enum
- **SQL Injection**: All values bound as parameters via Eloquent query builder
- **Relation Access Control**: Allow-list must explicitly include relation names

## Success Metrics

- Package is installable via Composer for Laravel 11/12/13
- All PHPUnit tests pass
- Zero SQL injection vulnerabilities via parameter binding
- Controller code reduced by ≥50% vs. manual query building
- Pagination works correctly for 1M+ row datasets

## Version History

| Version | Date | Notes |
|---------|------|-------|
| 2.0.0 | 2025-04 | Current stable; Laravel 13 support, fast-paginate removed |
| 1.x | 2024-xx | Previous major version |

## Dependencies & Versions

| Dependency | Version | Type |
|-----------|---------|------|
| `illuminate/support` | ^11.0 \| ^12.0 \| ^13.0 | Required |
| `illuminate/database` | ^11.0 \| ^12.0 \| ^13.0 | Required |
| `illuminate/http` | ^11.0 \| ^12.0 \| ^13.0 | Required |
| `illuminate/pagination` | ^11.0 \| ^12.0 \| ^13.0 | Required |
| `staudenmeir/belongs-to-through` | ^2.17 | Required |
| `phpunit/phpunit` | ^10.3 | Dev |
| `laravel/pint` | ^1.11 | Dev |
| `mockery/mockery` | ^1.6 | Dev |

## Future Enhancements (Potential)

- MongoDB filter style support (extend FilterStyle enum)
- Additional aggregate functions (median, percentile)
- Query result caching with cache invalidation strategy
- GraphQL schema generation from allow-lists
- OpenAPI/Swagger auto-documentation
- Performance metrics collection (query time tracking)

## Known Limitations

- Relationship sorting limited to models supported by BelongsToThrough
- Aggregates (`|count`, `|exists`) only on direct relationships, not nested
- Date filtering assumes column is datetime/date type; strings may have unexpected behavior
- Cursor pagination requires ORDERBY clause and may behave unexpectedly with complex filters
