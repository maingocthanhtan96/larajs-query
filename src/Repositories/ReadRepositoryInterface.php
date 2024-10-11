<?php

namespace LaraJS\Query\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * @template T
 */
interface ReadRepositoryInterface
{
    /**
     * @param  Request  $request
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $allows
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(Request $request, array $allows = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection;

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $allows
     * @return T
     */
    public function find(int $id, Request $request, array $allows = []);

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $allows
     * @return T
     */
    public function findOrFail(int $id, Request $request, array $allows = []);

    /**
     * @return Builder<T>
     */
    public function query(): Builder;
}
