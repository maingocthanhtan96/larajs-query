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

    private const DEFAULT_LIMIT = 25;

    private const MAX_LIMIT = 100;

    /**
     * @param  Model  $model
     */
    public function __construct(protected readonly Model $model) {}

    /**
     * @param  QueryParserAllowDTO  $allow
     * @param  array{limit?: array{default?: int, max?: int}}  $options
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(QueryParserAllowDTO $allow, array $options = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        $request = request();
        $defaultLimit = $options['limit']['default'] ?? config('larajs-query.limit.default', self::DEFAULT_LIMIT);
        $maxLimit = $options['limit']['max'] ?? config('larajs-query.limit.max', self::MAX_LIMIT);

        $queryBuilder = $this->getQueryWithLaraJS($allow);

        if ($request->input('pagination.page') === '-1') {
            return $queryBuilder->take(min($maxLimit, $request->input('pagination.limit')))->get();
        }

        $limit = min($request->input('pagination.limit', $defaultLimit), $maxLimit);

        return match ($request->input('pagination.type')) {
            'simple' => $queryBuilder->simplePaginate($limit, pageName: 'pagination[page]'),
            'cursor' => $queryBuilder->cursorPaginate($limit, cursorName: 'pagination[cursor]'),
            default => $queryBuilder->paginate($limit, pageName: 'pagination[page]'),
        };
    }

    /**
     * @param  string  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function find(string $id, QueryParserAllowDTO $allow)
    {
        return $this->getQueryWithLaraJS($allow, true)->find($id);
    }

    /**
     * @param  string  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function findOrFail(string $id, QueryParserAllowDTO $allow): Model
    {
        return $this->getQueryWithLaraJS($allow, true)->findOrFail($id);
    }

    /**
     * @return Builder<T>
     */
    public function query(): Builder
    {
        return $this->model->query();
    }

    /**
     * @param  QueryParserAllowDTO  $allow
     * @param  bool  $clearFilter
     * @return Builder
     */
    private function getQueryWithLaraJS(QueryParserAllowDTO $allow, bool $clearFilter = false): Builder
    {
        $query = request()->query();

        if ($clearFilter) {
            $query['filter'] = [];
        }

        return $this->applyLaraJSQuery(
            $this->query(),
            QueryParserRequestDTO::fromArray($query),
            $allow
        );
    }
}
