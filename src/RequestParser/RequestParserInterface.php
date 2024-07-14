<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface RequestParserInterface
{
    public function parse(Builder $query, Request $request): RequestParser;

    public function parseOption(Request $request): array;
}
