<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Support\Str;

class SortParser implements SortParserInterface
{
    public function parse(string $queryString): array
    {
        if (!$queryString) {
            return [];
        }

        return Str::of($queryString)->explode(',')->map(function ($pair) {
            [$field, $direction] = explode(' ', trim($pair)) + ['', 'asc'];

            return [$field, convert_direction($direction)];
        })->all();
    }
}
