<?php

namespace LaraJS\Query\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;
use LaraJS\Query\LaraJSQuery;

/**
 * @template T
 *
 * @implements ReadRepositoryInterface<T>
 */
class ReadRepository implements ReadRepositoryInterface
{
    use LaraJSQuery;

    /**
     * @param  Model  $model
     * @param  int  $limit
     * @param  int  $maxLimit
     */
    public function __construct(protected readonly Model $model, protected readonly int $limit, protected readonly int $maxLimit) {}

    /**
     * @param  QueryParserAllowDTO  $allow
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(QueryParserAllowDTO $allow): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        $request = request();
        $queryBuilder = $this->applyLaraJSQuery($this->query(), QueryParserRequestDTO::fromArray($request->query()), $allow);

        if ($request->input('pagination.page') === '-1') {
            $limit = min($this->maxLimit, $request->input('pagination.limit'));

            return $queryBuilder->take($limit)->get();
        }

        $limit = min($request->input('pagination.limit', $this->limit), $this->maxLimit);

        return match ($request->input('pagination.type')) {
            'simple' => $queryBuilder->simplePaginate($limit, pageName: 'pagination[page]'),
            'cursor' => $queryBuilder->cursorPaginate($limit, cursorName: 'pagination[cursor]'),
            default => $queryBuilder->paginate($limit, pageName: 'pagination[page]'),
        };
    }

    /**
     * @param  int  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function find(int $id, QueryParserAllowDTO $allow)
    {
        return $this->applyLaraJSQuery($this->query(), QueryParserRequestDTO::fromArray([...request()->query(), 'filter' => []]), $allow)->find($id);
    }

    /**
     * @param  int  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function findOrFail(int $id, QueryParserAllowDTO $allow): Model
    {
        return $this->applyLaraJSQuery($this->query(), QueryParserRequestDTO::fromArray([...request()->query(), 'filter' => []]), $allow)->findOrFail($id);
    }

    /**
     * @return Builder<T>
     */
    public function query(): Builder
    {
        return $this->model->query();
    }
}
