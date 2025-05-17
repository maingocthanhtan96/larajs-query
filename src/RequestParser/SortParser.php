<?php

namespace LaraJS\Query\RequestParser;

class SortParser
{
    /**
     * Sort parser
     *
     * @param  string  $queryString
     * @param  ?array<string>  $filterable
     * @return array
     */
    public function parse(string $queryString, ?array $filterable): array
    {
        if (!$queryString || (is_array($filterable) && empty($filterable))) {
            return [];
        }

        // Trim trailing commas and split by comma
        $fields = explode(',', trim($queryString, ','));
        $result = [];

        // Process each field
        foreach ($fields as $field) {
            // Skip empty fields
            if (trim($field) === '') {
                continue;
            }

            // Check if the field is allowed
            $cleanField = ltrim($field, '-');
            if ($filterable && !in_array($cleanField, $filterable, true)) {
                continue;
            }

            // Determine sort direction
            if (str_starts_with($field, '-')) {
                $result[] = [$cleanField, 'desc'];
            } else {
                $result[] = [$field, 'asc'];
            }
        }

        return $result;
    }
}
