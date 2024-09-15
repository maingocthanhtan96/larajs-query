<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface DateParserInterface
{
    public function parse(Builder $query, array $queryString): array;
}
