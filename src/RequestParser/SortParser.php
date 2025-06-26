<?php

namespace LaraJS\Query\RequestParser;

class SortParser
{
    public function parse(string $queryString, ?array $filterable): array
    {
        if (!$queryString || (is_array($filterable) && empty($filterable))) {
            return [];
        }

        return array_filter(
            array_map(function ($field) use ($filterable) {
                $field = trim($field);

                if (!$field) {
                    return null;
                }

                $cleanField = ltrim($field, '-');
                if ($filterable && !in_array($cleanField, $filterable, true)) {
                    return null;
                }

                return [
                    str_starts_with($field, '-') ? $cleanField : $field,
                    str_starts_with($field, '-') ? 'desc' : 'asc',
                ];
            }, explode(',', trim($queryString, ',')))
        );
    }
}
