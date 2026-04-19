<?php

namespace LaraJS\Query\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Znck\Eloquent\Relations\BelongsToThrough;

class OrderByRelationshipMacro
{
    public static function register(): void
    {
        if (!Builder::hasGlobalMacro('orderByRelationship')) {
            Builder::macro('orderByRelationship', function ($searchColumn, string $direction = 'asc') {
                if (Str::contains($searchColumn, '.')) {
                    [$relation, $column] = explode('.', $searchColumn);
                    $relation = $this->getRelation($relation);
                    if ($relation instanceof BelongsToMany) {
                        return OrderByRelationshipMacro::orderByBelongsToMany($this, $relation, $column, $direction);
                    }

                    if ($relation instanceof BelongsToThrough) {
                        return OrderByRelationshipMacro::orderByBelongsToThrough($this, $relation, $column, $direction);
                    }

                    $mainTable = $this->getModel()->getTable();
                    $relationForeignKey = $relation->getForeignKeyName();
                    $relationModel = $relation->getModel();
                    $relationTable = $relationModel->getTable();

                    return $this
                        ->leftJoin(
                            $relationTable,
                            "$mainTable.$relationForeignKey",
                            $relationModel->getQualifiedKeyName(),
                        )
                        ->orderBy("$relationTable.$column", $direction);
                }

                return $this->orderBy($searchColumn, $direction);
            });
        }
    }

    public static function orderByBelongsToMany(Builder $builder, BelongsToMany $relation, string $column, string $direction): Builder
    {
        $mainModel = $builder->getModel();
        $tableThrough = $relation->getTable();
        $relationForeignKey = $relation->getForeignPivotKeyName();
        $relationRelatedKey = $relation->getRelatedPivotKeyName();
        $relationModel = $relation->getModel();
        $relationTable = $relationModel->getTable();

        return $builder
            ->leftJoin(
                $tableThrough,
                "$tableThrough.$relationForeignKey",
                $mainModel->getQualifiedKeyName(),
            )
            ->leftJoin(
                $relationTable,
                "$tableThrough.$relationRelatedKey",
                $relationModel->getQualifiedKeyName(),
            )
            ->orderBy("$relationTable.$column", $direction);
    }

    public static function orderByBelongsToThrough(Builder $builder, BelongsToThrough $relation, string $column, string $direction): Builder
    {
        $joins = array_reverse($relation->getQuery()->getQuery()->joins);
        $queryTableRelated = $relation->getRelated()->getTable();
        foreach ($joins as $i => $join) {
            $where = $join->wheres[0];
            if ($i === 0) {
                $modelParent = $relation->getParent();
                $modelFirst = Arr::first(
                    $relation->getThroughParents(),
                    fn(Model $model) => $model->getTable() === $join->table,
                );
                $builder->leftJoin(
                    $join->table,
                    $modelParent->qualifyColumn($relation->getForeignKeyName($modelFirst)),
                    $modelFirst->getQualifiedKeyName(),
                );
            }
            $builder->leftJoin(explode('.', $where['second'])[0], $where['first'], '=', $where['second']);
        }

        return $builder->orderBy("$queryTableRelated.$column", $direction);
    }
}
