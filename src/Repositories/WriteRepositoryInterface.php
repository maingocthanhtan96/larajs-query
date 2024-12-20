<?php

namespace LaraJS\Query\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * @template T
 */
interface WriteRepositoryInterface
{
    /**
     * @param  array  $attributes
     * @return T
     */
    public function create(array $attributes);

    /**
     * @param  int|Model  $idOrModel
     * @param  array  $attributes
     * @param  array  $options
     * @return T
     */
    public function update(int|Model $idOrModel, array $attributes, array $options = []);

    /**
     * @param  int|Model  $idOrModel
     * @return bool
     */
    public function delete(int|Model $idOrModel): bool;
}
