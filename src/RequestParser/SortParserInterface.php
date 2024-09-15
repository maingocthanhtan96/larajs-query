<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface SortParserInterface
{
    public function parse(Builder $query, string $queryString): array;
}
