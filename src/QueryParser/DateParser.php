<?php

namespace LaraJS\QueryParser\QueryParser;

class DateParser implements DateParserInterface
{
    public function parse(array $queryString): array
    {
        $column = $queryString['column'] ?? '';
        $value = $queryString['value'] ?? [];
        if (!$column || !$value) {
            return [];
        }

        return [
            [
                'fx' => 'whereBetween',
                'isNested' => false,
                'parameters' => [
                    $column,
                    $value,
                ],
            ],
        ];
    }
}
