<?php

namespace LaraJS\Query;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;
use LaraJS\Query\QueryParser\QueryParserInterface;

trait LaraJSQuery
{
    /**
     * @param  Builder  $queryBuilder
     * @param  QueryParserRequestDTO  $options
     * @param  QueryParserAllowDTO  $allow
     * @return Builder
     */
    public function applyLaraJSQuery(Builder $queryBuilder, QueryParserRequestDTO $options, QueryParserAllowDTO $allow): Builder
    {
        return app(QueryParserInterface::class)->parse($queryBuilder, $options, $allow);
    }
}
