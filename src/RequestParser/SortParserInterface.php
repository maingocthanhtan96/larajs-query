<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface SortParserInterface
{
    public function parse(Builder $query, string $queryString): array;
}
