<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;

interface QueryParserInterface
{
    /**
     * @param  Builder  $query
     * @param  QueryParserRequestDTO  $options
     * @param  QueryParserAllowDTO  $allow
     * @return Builder
     */
    public function parse(Builder $query, QueryParserRequestDTO $options, QueryParserAllowDTO $allow): Builder;
}
