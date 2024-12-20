<?php

namespace LaraJS\Query\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaraJS\Query\DTO\QueryParserAllowDTO;

/**
 * @template T
 */
interface ReadRepositoryInterface
{
    /**
     * Find all
     *
     * @param  QueryParserAllowDTO  $allow
     * @param  array{limit: array{default: int, max: int}}  $options
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(QueryParserAllowDTO $allow, array $options = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection;

    /**
     * Find one
     *
     * @param  int  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function find(int $id, QueryParserAllowDTO $allow);

    /**
     * Find one or fail
     *
     * @param  int  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function findOrFail(int $id, QueryParserAllowDTO $allow);

    /**
     * Query
     *
     * @return Builder<T>
     */
    public function query(): Builder;
}
