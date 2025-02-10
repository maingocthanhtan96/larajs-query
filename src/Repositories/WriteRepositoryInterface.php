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
     * @param  string|Model  $idOrModel
     * @param  array  $attributes
     * @param  array  $options
     * @return T
     */
    public function update(string|Model $idOrModel, array $attributes, array $options = []);

    /**
     * @param  string|Model  $idOrModel
     * @return bool
     */
    public function delete(string|Model $idOrModel): bool;
}
