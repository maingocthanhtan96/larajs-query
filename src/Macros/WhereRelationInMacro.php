<?php

namespace LaraJS\Query\Macros;

use Illuminate\Database\Eloquent\Builder;

class WhereRelationInMacro
{
    public static function register(): void
    {
        if (!Builder::hasGlobalMacro('whereRelationIn')) {
            Builder::macro('whereRelationIn', function ($relation, $column, $values) {
                return $this->whereHas($relation, function ($query) use ($column, $values) {
                    $query->whereIn($column, $values);
                });
            });
        }
    }
}
