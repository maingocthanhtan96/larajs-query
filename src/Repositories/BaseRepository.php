<?php

namespace LaraJS\Query\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaraJS\Query\DTO\QueryParserAllowDTO;

/**
 * @template T
 *
 * @implements ReadRepositoryInterface<T>
 * @implements WriteRepositoryInterface<T>
 */
class BaseRepository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    private readonly WriteRepository $writeRepository;

    private readonly ReadRepository $readRepository;

    /**
     * @param  Model  $model
     */
    public function __construct(protected readonly Model $model)
    {
        $this->writeRepository = new WriteRepository($this->model);
        $this->readRepository = new ReadRepository($this->model);
    }

    /**
     * @param  QueryParserAllowDTO  $allow
     * @param  array{limit: array{default: int, max: int}}  $options
     * @return LengthAwarePaginator|CursorPaginator|Paginator|Collection<int, T>
     */
    public function findAll(QueryParserAllowDTO $allow, array $options = []): LengthAwarePaginator|CursorPaginator|Paginator|Collection
    {
        return $this->readRepository->findAll($allow, $options);
    }

    /**
     * @param  int  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function find(int $id, QueryParserAllowDTO $allow)
    {
        return $this->readRepository->find($id, $allow);
    }

    /**
     * @param  int  $id
     * @param  QueryParserAllowDTO  $allow
     * @return T
     */
    public function findOrFail(int $id, QueryParserAllowDTO $allow)
    {
        return $this->readRepository->findOrFail($id, $allow);
    }

    /**
     * @return Builder<T>
     */
    public function query(): Builder
    {
        return $this->readRepository->query();
    }

    /**
     * @param  array  $attributes
     * @return T
     */
    public function create(array $attributes)
    {
        return $this->writeRepository->create($attributes);
    }

    /**
     * @param  int|Model  $idOrModel
     * @param  array  $attributes
     * @param  array  $options
     * @return T
     */
    public function update(Model|int $idOrModel, array $attributes, array $options = [])
    {
        return $this->writeRepository->update($idOrModel, $attributes, $options);
    }

    /**
     * @param  int|Model  $idOrModel
     * @return bool
     */
    public function delete(Model|int $idOrModel): bool
    {
        return $this->writeRepository->delete($idOrModel);
    }
}
