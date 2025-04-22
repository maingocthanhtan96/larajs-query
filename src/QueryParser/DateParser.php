<?php

namespace LaraJS\Query\QueryParser;

use Carbon\Carbon;
use LaraJS\Query\Enum\Method;

class DateParser
{
    public function parse(array $queryString): array
    {
        $column = $queryString['column'] ?? [];
        $value = $queryString['value'] ?? [];
        if (!$column || !$value) {
            return [];
        }

        return [
            [
                'fx' => Method::DATE_BETWEEN->value,
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
