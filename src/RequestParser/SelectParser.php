<?php

namespace LaraJS\Query\RequestParser;

use InvalidArgumentException;

class SelectParser
{
    public function parse(string $queryString, ?array $filterable): array
    {
        if (!$queryString) {
            return $filterable ?? [];
        }

        if (is_array($filterable) && empty($filterable)) {
            return [];
        }

        $fields = array_values(array_filter(array_map('trim', explode(',', trim($queryString, ',')))));

        foreach ($fields as $field) {
            if ($filterable && !in_array($field, $filterable, true)) {
                throw new InvalidArgumentException("Field '{$field}' is not filterable.");
            }
        }

        return $fields;
    }
}
