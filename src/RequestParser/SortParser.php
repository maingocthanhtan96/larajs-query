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

        return Str::of($queryString)->explode(',')->filter(fn ($pair) => $pair)->map(function ($pair) {
            if (str_starts_with($pair, '-')) {
                return [substr($pair, 1), 'desc'];
            }
            return [$pair, 'asc'];
        })->all();
    }
}
