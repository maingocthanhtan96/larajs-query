<?php

namespace LaraJS\Query\QueryParser;

use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use LaraJS\Query\Enum\Method;

class DateParser
{
    public function parse(array $queryString): array
    {
        $column = $queryString['column'] ?? '';
        $value = $queryString['value'] ?? [];

        if (!$column || !$value) {
            return [];
        }

        try {
            return [[
                'fx' => Method::BETWEEN->value,
                'isNested' => false,
                'parameters' => [
                    $column,
                    [
                        Carbon::parse($value['start'] ?? $value[0])->startOfDay(),
                        Carbon::parse($value['end'] ?? $value[1])->endOfDay(),
                    ],
                ],
            ]];
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid date format: {$e->getMessage()}");
        }
    }
}
