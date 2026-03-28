<?php

namespace LaraJS\Query\RequestParser;

class DateParser
{
    public function parse(array $queryString, ?array $filterable): array
    {
        if (!$queryString || (is_array($filterable) && empty($filterable))) {
            return ['column' => '', 'value' => []];
        }

        $column = $queryString['column'] ?? '';

        if ($filterable && !in_array($column, $filterable, true)) {
            return ['column' => '', 'value' => []];
        }

        return [
            'column' => $column,
            'value' => $queryString['value'] ?? [],
        ];
    }
}
