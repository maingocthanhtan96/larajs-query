<?php

namespace LaraJS\Query\RequestParser;

class SearchParser
{
    public function parse(array $queryString, ?array $filterable): array
    {
        $defaultData = ['column' => '', 'value' => ''];

        if ((is_array($filterable) && empty($filterable)) || empty($queryString['column'] ?? '')) {
            return $defaultData;
        }

        $columns = array_filter(
            array_map('trim', explode(',', trim($queryString['column'], ','))),
            fn($value) => $value && (!$filterable || in_array(strtok($value, '.'), $filterable, true))
        );

        return $columns ? [
            'column' => $columns,
            'value' => $queryString['value'] ?? '',
        ] : $defaultData;
    }
}
