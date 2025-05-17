<?php

namespace LaraJS\Query\QueryParser;

use Carbon\Carbon;
use InvalidArgumentException;
use LaraJS\Query\Enum\Method;

class DateParser
{
    /**
     * Parse date query string
     *
     * @param  array  $queryString
     * @return array
     *
     * @throws InvalidArgumentException If date format is invalid
     */
    public function parse(array $queryString): array
    {
        $column = $queryString['column'] ?? '';
        $value = $queryString['value'] ?? [];
        if (!$column || !$value) {
            return [];
        }

        $startDate = $value['start'] ?? $value[0];
        $endDate = $value['end'] ?? $value[1];

        try {
            // Parse dates only once and store in variables
            $parsedStartDate = Carbon::parse($startDate)->startOfDay();
            $parsedEndDate = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid date format: {$e->getMessage()}");
        }

        return [
            [
                'fx' => Method::BETWEEN->value,
                'isNested' => false,
                'parameters' => [
                    $column,
                    [
                        $parsedStartDate,
                        $parsedEndDate,
                    ],
                ],
            ],
        ];
    }
}
