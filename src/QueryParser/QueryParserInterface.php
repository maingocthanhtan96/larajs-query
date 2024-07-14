<?php

namespace LaraJS\QueryParser\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface QueryParserInterface
{
    public function parse(Builder $query, Request $request): Builder;
}
