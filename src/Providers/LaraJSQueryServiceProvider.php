<?php

namespace LaraJS\Query\Providers;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use LaraJS\Query\QueryParser\DateParser;
use LaraJS\Query\QueryParser\FilterParser;
use LaraJS\Query\QueryParser\IncludeParser;
use LaraJS\Query\QueryParser\QueryParser;
use LaraJS\Query\QueryParser\QueryParserInterface;
use LaraJS\Query\QueryParser\SearchParser;
use LaraJS\Query\QueryParser\SelectParser;
use LaraJS\Query\QueryParser\SortParser;
use LaraJS\Query\RequestParser\RequestParser;
use Znck\Eloquent\Relations\BelongsToThrough;

class LaraJSQueryServiceProvider extends ServiceProvider
{
    protected bool $defer = false;

    public function register(): void
    {
        $this->app->singleton(QueryParserInterface::class, function (Application $app) {
            return new QueryParser(
                $app->make(RequestParser::class),
                $app->make(FilterParser::class),
                $app->make(SortParser::class),
                $app->make(IncludeParser::class),
                $app->make(SelectParser::class),
                $app->make(SearchParser::class),
                $app->make(DateParser::class),
            );
        });
        $this->whereLikeRelationship();
        $this->paginate();
        $this->orderByRelationship();
    }

    private function whereLikeRelationship(): void
    {
        // whereLike
        if (!Builder::hasGlobalMacro('whereLikeRelationship')) {
            Builder::macro('whereLikeRelationship', function ($attributes, string $searchTerm) {
                $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                    foreach (Arr::wrap($attributes) as $attribute) {
                        $query->when(
                            // Check if the attribute is not an expression and contains a dot (indicating a related model)
                            !($attribute instanceof Expression) && str_contains((string) $attribute, '.'),
                            function (Builder $query) use ($attribute, $searchTerm) {
                                // Split the attribute into a relation and related attribute
                                [$relation, $relatedAttribute] = explode('.', (string) $attribute);

                                // Perform a 'LIKE' search on the related model's attribute
                                $relationModel = $this->getRelation($relation)->getModel();
                                $relationTable = $relationModel->getTable();
                                $query->orWhereHas($relation, function (Builder $query) use (
                                    $relatedAttribute,
                                    $searchTerm,
                                    $relationTable,
                                ) {
                                    $query->where("$relationTable.$relatedAttribute", 'LIKE', "%{$searchTerm}%");
                                });
                            },
                            function (Builder $query) use ($attribute, $searchTerm) {
                                // Perform a 'LIKE' search on the current model's attribute
                                // also attribute can be an expression
                                $table = $this->getModel()->getTable();
                                $query->orWhere("$table.$attribute", 'LIKE', "%{$searchTerm}%");
                            },
                        );
                    }
                });

                return $this;
            });
        }
    }

    private function paginate(): void
    {
        // Enable pagination
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage)
                        ->values()
                        ->all(),
                    $this->count(),
                    $perPage,
                    $page,
                    $options,
                ))->withPath(Request::url());
            });
        }
    }

    private function orderByRelationship(): void
    {
        if (!Builder::hasGlobalMacro('orderByRelationship')) {
            Builder::macro('orderByRelationship', function ($searchColumn, string $direction = 'asc') {
                if (Str::contains($searchColumn, '.')) {
                    [$relation, $column] = explode('.', $searchColumn);
                    $relation = $this->getRelation($relation);
                    if ($relation instanceof BelongsToMany) {
                        $mainModel = $this->getModel();
                        $mainTable = $mainModel->getTable();
                        $tableThrough = $relation->getTable();
                        $relationForeignKey = $relation->getForeignPivotKeyName();
                        $relationRelatedKey = $relation->getRelatedPivotKeyName();
                        $relationModel = $relation->getModel();
                        $relationTable = $relationModel->getTable();

                        return $this
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

                    if ($relation instanceof BelongsToThrough) {
                        $queryTable = $this->getModel()->getTable();
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
                                $this->leftJoin(
                                    $join->table,
                                    $modelParent->qualifyColumn($relation->getForeignKeyName($modelFirst)),
                                    $modelFirst->getQualifiedKeyName(),
                                );
                            }
                            $this->leftJoin(explode('.', $where['second'])[0], $where['first'], '=', $where['second']);
                        }

                        return $this->orderBy("$queryTableRelated.$column", $direction);
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
}
