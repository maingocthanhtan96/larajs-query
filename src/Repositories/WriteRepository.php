<?php

namespace LaraJS\Query\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * @template T
 *
 * @implements WriteRepositoryInterface<T>
 */
class WriteRepository implements WriteRepositoryInterface
{
    /**
     * @param  Model  $model
     */
    public function __construct(protected readonly Model $model) {}

    /**
     * @param  array  $attributes
     * @return T
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * @param  string|Model  $idOrModel
     * @param  array  $attributes
     * @param  array  $options
     * @return T
     */
    public function update(string|Model $idOrModel, array $attributes, array $options = []): Model
    {
        $model = $this->resolveModel($idOrModel);
        $model->update($attributes, $options);

        return $model;
    }

    /**
     * @param  string|Model  $idOrModel
     * @return bool
     */
    public function delete(string|Model $idOrModel): bool
    {
        return $this->resolveModel($idOrModel)->delete();
    }

    /**
     * @param  string|Model  $idOrModel
     * @return Model
     */
    private function resolveModel(string|Model $idOrModel): Model
    {
        return $idOrModel instanceof Model ? $idOrModel : $this->model->findOrFail($idOrModel);
    }
}
