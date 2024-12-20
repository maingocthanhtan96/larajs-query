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
     * @param  int|Model  $idOrModel
     * @param  array  $attributes
     * @param  array  $options
     * @return T
     */
    public function update(int|Model $idOrModel, array $attributes, array $options = [])
    {
        if (is_int($idOrModel)) {
            $model = $this->model->findOrFail($idOrModel);
        } else {
            $model = $idOrModel;
        }

        $model->update($attributes, $options);

        return $model;
    }

    /**
     * @param  int|Model  $idOrModel
     * @return bool
     */
    public function delete(int|Model $idOrModel): bool
    {
        if (is_int($idOrModel)) {
            $model = $this->model->findOrFail($idOrModel);
        } else {
            $model = $idOrModel;
        }

        return $model->delete();
    }
}
