<?php

namespace LaraJS\QueryParser\QueryParser;

use Illuminate\Support\Str;

class FieldParser implements FieldParserInterface
{
    public function parse(string $queryString): array
    {
        if (!$queryString) {
            return [];
        }

        return [
            [
                'fx' => 'select',
                'isNested' => false,
                'parameters' => Str::of($queryString)->explode(',')->map(fn ($value) => trim($value))->all(),
            ],
        ];
    }
}
