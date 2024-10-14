<?php

namespace LaraJS\Query\QueryParser;

use Carbon\Carbon;

class DateParser
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
                    [
                        Carbon::createFromFormat('Y-m-d', $value[0])->startOfDay(),
                        Carbon::createFromFormat('Y-m-d', $value[1])->endOfDay(),
                    ],
                ],
            ],
        ];
    }
}
