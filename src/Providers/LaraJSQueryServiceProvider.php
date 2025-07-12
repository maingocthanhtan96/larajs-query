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

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../../config/larajs-query.php' => config_path('larajs-query.php'),
            ],
            'larajs-query',
        );
        $this->mergeConfigFrom(__DIR__ . '/../../config/larajs-query.php', 'larajs-query');
    }

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
        $this->whereRelationIn();
        $this->whereRelationBetween();
        $this->whereLikeRelationship();
        $this->collectionPaginate();
        $this->orderByRelationship();
        $this->dynamicPaginate();
    }

    private function whereRelationIn(): void
    {
        if (!Builder::hasGlobalMacro('whereRelationIn')) {
            Builder::macro('whereRelationIn', function ($relation, $column, $values) {
                return $this->whereHas($relation, function ($query) use ($column, $values) {
                    $query->whereIn($column, $values);
                });
            });
        }
    }

    private function whereRelationBetween(): void
    {
        if (!Builder::hasGlobalMacro('whereRelationBetween')) {
            Builder::macro('whereRelationBetween', function ($relation, $column, $values) {
                return $this->whereHas($relation, function ($query) use ($column, $values) {
                    $query->whereBetween($column, $values);
                });
            });
        }
    }

    private function whereLikeRelationship(): void
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
                                $subQuery->where("$relationTable.$relatedAttribute", 'LIKE', $searchPattern);
                            });
                        } else {
                            $query->orWhere("$currentTable.$attribute", 'LIKE', $searchPattern);
                        }
                    }
                });

                return $this;
            });
        }
    }

    private function collectionPaginate(): void
    {
        if (!Collection::hasMacro('collectionPaginate')) {
            Collection::macro('collectionPaginate', function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage)->values()->all(),
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

    private function dynamicPaginate(): void
    {
        if (!Builder::hasGlobalMacro('dynamicPaginate')) {
            Builder::macro('dynamicPaginate', function (array $options = []) {
                $request = request();
                $defaultLimit = $options['limit']['default'] ?? config('larajs-query.limit.default', 25);
                $maxLimit = $options['limit']['max'] ?? config('larajs-query.limit.max', 500);
                $limit = min($request->input('pagination.limit', $defaultLimit), $maxLimit);

                if ($request->input('pagination.page') === '-1') {
                    return $this->take($limit)->get();
                }

                return match ($request->input('pagination.type')) {
                    'cursor' => $this->cursorPaginate(perPage: $limit, cursorName: 'pagination.cursor')->setCursorName('pagination[cursor]')->appends($request->except('pagination.cursor')),
                    'simple' => $this->simpleFastPaginate(perPage: $limit, pageName: 'pagination.page')->setPageName('pagination[page]')->appends($request->except('pagination.page')),
                    default => $this->fastPaginate(perPage: $limit, pageName: 'pagination.page')->setPageName('pagination[page]')->appends($request->except('pagination.page')),
                };
            });
        }
    }
}
