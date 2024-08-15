<?php

namespace LaraJS\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaraJS\QueryParser\QueryParser\QueryParserInterface;

trait LaraJSQueryParser
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
