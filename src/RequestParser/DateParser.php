<?php

namespace LaraJS\Query\RequestParser;

class DateParser
{
    /**
     * Date parser
     *
     * @param  array  $queryString
     * @param  array<string>  $filterable
     * @return array
     */
    public function parse(array $queryString, array $filterable): array
    {
        if (!$queryString) {
            return [
                'column' => '',
                'value' => [],
            ];
        }
        $column = $queryString['column'] ?? '';

        if ($filterable && !in_array($column, $filterable, true)) {
            return [];
        }

        return [
            'column' => $column,
            'value' => $queryString['value'] ?? [],
        ];
    }
}
