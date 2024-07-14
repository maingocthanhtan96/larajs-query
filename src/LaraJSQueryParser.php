<?php

namespace LaraJS\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaraJS\QueryParser\QueryParser\QueryParserInterface;

trait LaraJSQueryParser
{
    public function applyQueryBuilder(Builder $queryBuilder, Request $request): Builder
    {
        return app(QueryParserInterface::class)->parse($queryBuilder, $request);
    }
}
