<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Support\Str;

class FieldParser implements FieldParserInterface
{
    public function parse(string $queryString): array
    {
        if (!$queryString) {
            return [];
        }

        return Str::of($queryString)->trim(',')->explode(',')->map(fn ($value) => trim($value))->all();
    }
}
