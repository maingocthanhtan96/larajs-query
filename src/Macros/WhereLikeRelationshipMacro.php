<?php

namespace LaraJS\Query\Macros;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class WhereLikeRelationshipMacro
{
    public static function register(): void
    {
        if (!Builder::hasGlobalMacro('whereLikeRelationship')) {
            Builder::macro('whereLikeRelationship', function ($attributes, string $searchTerm) {
                $searchPattern = "%{$searchTerm}%";
                $currentTable = $this->getModel()->getTable();

                $this->where(function (Builder $query) use ($attributes, $searchPattern, $currentTable) {
                    foreach (Arr::wrap($attributes) as $attribute) {
                        $isRelation = !($attribute instanceof Expression) && is_string($attribute) && str_contains($attribute, '.');

                        if ($isRelation) {
                            [$relation, $relatedAttribute] = explode('.', $attribute, 2);
                            $relationModel = $this->getRelation($relation)->getModel();
                            $relationTable = $relationModel->getTable();

                            $query->orWhereHas($relation, function (Builder $subQuery) use (
                                $relatedAttribute,
                                $searchPattern,
                                $relationTable
                            ) {
                                $subQuery->whereLike("$relationTable.$relatedAttribute", $searchPattern);
                            });
                        } else {
                            $query->orWhereLike("$currentTable.$attribute", $searchPattern);
                        }
                    }
                });

                return $this;
            });
        }
    }
}
