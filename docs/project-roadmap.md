# Project Roadmap

## Current Status: v2.0.0 (Stable)

**Release Date**: April 2025
**Stability**: Production Ready
**Laravel Support**: 11, 12, 13
**PHP Support**: 8.3+

## Completed Features (v2.0.0)

### Core Filtering
- [x] IBM-style functional filter syntax with 15+ operators
- [x] Simple operators: equals, lessThan, greaterThan, lessOrEqual, greaterOrEqual
- [x] String operators: contains, startsWith, endsWith, any
- [x] Range operator: between
- [x] Logical operators: and, or, not
- [x] Relationship operators: has, relation
- [x] Relationship comparison: equalsRelation, greaterThanRelation, etc.

### Sorting
- [x] Single & multi-column sorting
- [x] Ascending/descending order
- [x] Relationship sorting via BelongsToThrough
- [x] Relationship count sorting (roles_count)

### Searching
- [x] LIKE-based search
- [x] Multi-column search
- [x] Relationship search

### Relationship Eager Loading
- [x] Basic with() relationship inclusion
- [x] Nested relationship loading
- [x] Aggregate functions: count, exists, sum, min, max, avg
- [x] Filtered relationship includes
- [x] Relationship constraint parsing

### Field Selection
- [x] Column projection via select parameter
- [x] Multiple column selection

### Date Filtering
- [x] Auto-calculated date boundaries (startOfDay, endOfDay)
- [x] whereBetween application
- [x] Multiple date range formats

### Pagination
- [x] Default pagination (page-based)
- [x] Simple pagination
- [x] Cursor pagination
- [x] Configurable limit (default 25, max 500)

### Security & Validation
- [x] Allow-list whitelisting for fields, relations, sorts, searches
- [x] SQL injection prevention via parameter binding
- [x] Operator validation against IbmOperator enum
- [x] Relation access control via allow-lists

### Code Quality
- [x] Full type hints (PHP 8.3+)
- [x] Comprehensive PHPUnit test suite
- [x] Laravel Pint linting
- [x] Repository pattern implementation
- [x] Service provider registration

### Documentation
- [x] README.md with usage examples
- [x] Filter operator reference table
- [x] Sort/search/include examples
- [x] Pagination documentation
- [x] Allow-list configuration guide
- [x] Repository pattern documentation

## Short-term Enhancements (v2.1.0 - Q3 2025)

### Performance Optimizations
- [ ] Query result caching layer (Redis/file-based)
- [ ] Parsed filter expression caching
- [ ] Compiled query builder cache
- [ ] Benchmark suite for common queries
- [ ] Query execution time logging

**Effort**: Medium | **Impact**: Medium | **Priority**: Medium

### Extended Operators
- [ ] `isBetween()` as inverse of `between()`
- [ ] `isNull()` / `isNotNull()` operators
- [ ] `exists()` operator (relationship existence check)
- [ ] `in()` as alias for `any()`
- [ ] `regexp()` for regex pattern matching
- [ ] `soundsLike()` for SOUNDEX/metaphone matching

**Effort**: Low | **Impact**: Low | **Priority**: Low

### Relationship Enhancements
- [ ] Support for HasMany through relationships
- [ ] HasOneThrough sorting
- [ ] Filtered aggregates: count(created_at > '2025-01-01')
- [ ] Multiple aggregate functions per include
- [ ] Recursive relationship depth limits

**Effort**: Medium | **Impact**: Medium | **Priority**: Medium

### Developer Experience
- [ ] Interactive query builder CLI tool
- [ ] Query string generator (inverse of parser)
- [ ] Debug toolbar integration (Laravel Debugbar)
- [ ] Query analyzer (suggests optimal indexes)
- [ ] Schema introspection for auto-complete hints

**Effort**: Medium | **Impact**: Medium | **Priority**: Medium

## Medium-term Features (v2.5.0 - Q4 2025)

### MongoDB Filter Style
- [ ] Extend FilterStyle enum to include MongoDB
- [ ] MongoDB filter parser: {name: 'Smith', age: {$gt: 25}}
- [ ] MongoDB operator mapping
- [ ] Auto-detect database type from model

**Effort**: High | **Impact**: High | **Priority**: Medium

### GraphQL Schema Generation
- [ ] Auto-generate GraphQL types from allow-lists
- [ ] Query resolver using LaraJSQuery
- [ ] Mutation support for WriteRepository
- [ ] Subscription support for real-time updates

**Effort**: High | **Impact**: High | **Priority**: Low

### OpenAPI/Swagger Documentation
- [ ] Auto-generate Swagger from allow-lists
- [ ] Document filter operators in API spec
- [ ] Parameter validation rules in spec
- [ ] Example requests/responses

**Effort**: Medium | **Impact**: High | **Priority**: Medium

### Advanced Filtering
- [ ] Full-text search support (MATCH AGAINST for MySQL)
- [ ] Spatial queries (ST_Contains, ST_Distance for geo data)
- [ ] JSON column filtering (SQLite JSON1, MySQL JSON functions)
- [ ] Case-insensitive filtering options

**Effort**: High | **Impact**: Medium | **Priority**: Low

## Long-term Vision (v3.0.0 - 2026)

### Architecture Refactoring
- [ ] Request parsing as pipeline service
- [ ] Query building as fluent builder interface
- [ ] Abstract database layer (support non-Eloquent drivers)
- [ ] Plugin system for custom operators/parsers

**Effort**: Very High | **Impact**: High | **Priority**: Low

### Caching Strategy
- [ ] Query result caching with cache keys
- [ ] Automatic cache invalidation on model changes
- [ ] Cache warming for common queries
- [ ] Cache statistics & analytics

**Effort**: High | **Impact**: Medium | **Priority**: Low

### Performance Monitoring
- [ ] Slow query detection
- [ ] Missing index suggestions
- [ ] N+1 query detection
- [ ] Performance metrics dashboard (standalone)

**Effort**: Medium | **Impact**: High | **Priority**: Low

### Client-side Libraries
- [ ] JavaScript/TypeScript query builder
- [ ] React hook for LaraJS Query
- [ ] Vue 3 composable
- [ ] Svelte store
- [ ] Auto-validate against server schema

**Effort**: Very High | **Impact**: High | **Priority**: Medium

## Known Limitations

### Current (May require redesign)
1. **Recursion Depth**: Filter parsing unbounded (potential stack overflow)
   - **Mitigation**: Add max recursion depth parameter (v2.1)
   - **Solution**: Iterative parser implementation (v3.0)

2. **Aggregate Nesting**: Only on direct relationships, not chains
   - **Example**: `users.posts.comments|count` not supported
   - **Solution**: Recursive aggregate support (v2.5)

3. **Date Column Assumptions**: Must be datetime/date type
   - **Problem**: String date columns behave unexpectedly
   - **Solution**: Type detection in DateParser (v2.1)

4. **Cursor Pagination Edge Cases**: May fail with complex filters
   - **Problem**: Requires ORDERBY, undefined with certain conditions
   - **Solution**: Hybrid pagination strategy (v2.5)

5. **Relationship Sorting Limits**: Chained through relationships only
   - **Problem**: HasMany through not supported
   - **Solution**: Full relationship graph support (v3.0)

## Deprecation Schedule

### Planned Deprecations
- **v2.2**: `fast-paginate` integration (removed in v2.0 via native pagination)
- **v3.0**: Deprecated operator functions (currently none planned)

## Dependencies & Compatibility

### Current
| Dependency | Min Version | Status |
|-----------|------------|--------|
| PHP | 8.3 | Required |
| Laravel | 11.0 | Required |
| Laravel | 12.0 | Supported |
| Laravel | 13.0 | Supported |
| staudenmeir/belongs-to-through | 2.17 | Required |

### Planned Support
- Laravel 14.0 (when released, 2025-12)
- Laravel 15.0 (when released, 2026-12)
- PHP 8.4 (when released, 2024-11)

## Testing Roadmap

| Phase | Coverage | Target | Status |
|-------|----------|--------|--------|
| v2.0.0 | Core parsers + builders | 80% | Complete |
| v2.1.0 | Edge cases + performance | 85% | Planned |
| v2.5.0 | New features | 90% | Planned |
| v3.0.0 | Full coverage | 95% | Planned |

## Documentation Roadmap

### v2.0.0 (Current)
- [x] README.md with quick start
- [x] Filter operator reference
- [x] Usage examples
- [x] Repository pattern guide
- [x] Code standards

### v2.1.0
- [ ] Troubleshooting guide
- [ ] Performance tuning guide
- [ ] FAQ & common pitfalls
- [ ] Migration guide (v1→v2)

### v2.5.0
- [ ] MongoDB filter guide
- [ ] GraphQL schema generation guide
- [ ] Swagger/OpenAPI guide
- [ ] Advanced filtering cookbook

### v3.0.0
- [ ] Complete API reference (auto-generated from PHPDoc)
- [ ] Architecture deep-dive
- [ ] Plugin development guide
- [ ] Performance benchmarks

## Community & Feedback

### Feedback Channels
- GitHub Issues: Bug reports & feature requests
- GitHub Discussions: Ideas & general questions
- Email: maingocthanhtan96@gmail.com

### Contribution Guidelines
- Follow code standards (PHP 8.3+, Laravel Pint)
- Add tests for new features (≥75% coverage)
- Update documentation
- Use conventional commit messages

## Release Schedule

| Version | ETA | Status |
|---------|-----|--------|
| 2.0.0 | Apr 2025 | Released |
| 2.1.0 | Jul 2025 | Planned |
| 2.5.0 | Oct 2025 | Planned |
| 3.0.0 | Q2 2026 | Planned |

## Metrics & Success Criteria

### Adoption
- Target: 1,000+ Composer downloads/month by end of 2025
- Current: Growing
- Metric: Packagist download stats

### Quality
- Target: ≥90% test coverage
- Current: ~85%
- Metric: PHPUnit + code coverage tools

### Performance
- Target: <5ms query building per request
- Current: <3ms typical
- Metric: Benchmark suite (to be added v2.1)

### Community
- Target: 50+ GitHub stars by end of 2025
- Current: Growing
- Metric: GitHub repository stats

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Breaking Laravel API changes | Low | High | Test matrix against L11/12/13 |
| Performance regression | Medium | Medium | Add performance benchmarks (v2.1) |
| Filter syntax conflicts | Low | High | Enum-based operators prevent typos |
| Relationship edge cases | Medium | Medium | Expand test coverage (ongoing) |
| Security vulnerabilities | Low | High | Regular dependency audits, parameter binding |

## Sponsor & Support

**Maintainer**: tanmnt (maingocthanhtan96@gmail.com)

**Support Levels**:
- Free: GitHub Issues, community support
- Sponsored: Custom features, priority support (inquiry via email)

---

**Last Updated**: April 18, 2025
**Next Review**: July 2025
