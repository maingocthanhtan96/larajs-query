<?php

namespace LaraJS\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaraJS\Query\QueryParser\QueryParserInterface;

trait LaraJSQuery
{
    /**
     * @param  Builder  $queryBuilder
     * @param  ?Request  $request
     * @return Builder
     */
    public function applyQueryBuilder(Builder $queryBuilder, ?Request $request = null): Builder
    {
        return app(QueryParserInterface::class)->parse($queryBuilder, $request ?? request());
    }
}
