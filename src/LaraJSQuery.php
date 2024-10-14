<?php

namespace LaraJS\Query;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\QueryParser\QueryParserInterface;

trait LaraJSQuery
{
    /**
     * @param  Builder  $queryBuilder
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $options
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $allows
     * @return Builder
     */
    public function applyLaraJSQuery(Builder $queryBuilder, array $options = [], array $allows = []): Builder
    {
        return app(QueryParserInterface::class)->parse($queryBuilder, $options, $allows);
    }
}
