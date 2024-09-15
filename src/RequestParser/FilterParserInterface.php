<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface FilterParserInterface
{
    public function parse(Builder $query, string|array $queryString): array;
}
