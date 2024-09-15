<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;

interface FieldParserInterface
{
    public function parse(Builder $query, string $queryString): array;
}
