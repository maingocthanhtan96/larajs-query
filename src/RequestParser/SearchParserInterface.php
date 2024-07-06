<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface SearchParserInterface
{
    public function parse(Builder $query, array $queryString): array;
}
