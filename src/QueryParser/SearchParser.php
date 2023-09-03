<?php

namespace LaraJS\QueryParser\QueryParser;

use Illuminate\Support\Str;

class SearchParser implements SearchParserInterface
{
    public function parse(array $queryString): array
    {
        $column = $queryString['column'] ?? '';
        $value = $queryString['value'] ?? '';
        if (!$column || !$value) {
            return [];
        }

        return [
            [
                'fx' => 'whereLike',
                'isNested' => false,
                'parameters' => [
                    Str::of($column)
                        ->explode(',')
                        ->map(fn ($value) => trim($value))
                        ->all(),
                    $value,
                ],
            ]
        ];
    }
}
