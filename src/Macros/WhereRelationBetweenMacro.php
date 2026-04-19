<?php

namespace LaraJS\Query\Macros;

use Illuminate\Database\Eloquent\Builder;

class WhereRelationBetweenMacro
{
    public static function register(): void
    {
        if (!Builder::hasGlobalMacro('whereRelationBetween')) {
            Builder::macro('whereRelationBetween', function ($relation, $column, $values) {
                return $this->whereHas($relation, function ($query) use ($column, $values) {
                    $query->whereBetween($column, $values);
                });
            });
        }
    }
}
