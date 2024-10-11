<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Database\Eloquent\Builder;

interface QueryParserInterface
{
    /**
     * @param  Builder  $query
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $options
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $allows
     * @return Builder
     */
    public function parse(Builder $query, array $options, array $allows): Builder;
}
