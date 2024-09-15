<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface IncludeParserInterface
{
    public function parse(Builder $query, array $queryString): array;
}
