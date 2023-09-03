<?php

namespace LaraJS\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaraJS\QueryParser\QueryParser\QueryParserInterface;

trait LaraJSQueryParser
{
    public function applyQueryBuilder(Builder $queryBuilder, Request $request, array $option = []): Builder
    {
        $parser = app(QueryParserInterface::class);

        return $parser->parse($queryBuilder, $request, $option);
    }
}
