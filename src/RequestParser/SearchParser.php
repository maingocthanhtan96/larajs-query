<?php

namespace LaraJS\Query\RequestParser;

class SearchParser
{
    /**
     * Parse the search query.
     *
     * @param  array  $queryString
     * @param  ?array{column: array<string>, value: string}  $filterable
     * @return array{column: array<string>, value: string}
     */
    public function parse(array $queryString, ?array $filterable): array
    {
        $defaultData = [
            'column' => '',
            'value' => '',
        ];

        // Return default data if filterable is empty
        if (is_array($filterable) && empty($filterable)) {
            return $defaultData;
        }

        // Get column string from query or use empty string
        $columnString = $queryString['column'] ?? '';

        // Return default data if column string is empty
        if (empty($columnString)) {
            return $defaultData;
        }

        // Trim trailing commas and split by comma
        $columnParts = explode(',', trim($columnString, ','));
        $columns = [];

        // Process each column
        foreach ($columnParts as $value) {
            // Trim whitespace
            $value = trim($value);

            // Skip empty values
            if (!$value) {
                continue;
            }

            // Extract base field name (before the dot)
            $field = explode('.', $value)[0] ?? null;

            // Skip if the field is empty or not in the filterable list
            if (!$field || ($filterable && !in_array($field, $filterable, true))) {
                continue;
            }

            $columns[] = $value;
        }

        // Return default data if no valid columns are found
        if (!$columns) {
            return $defaultData;
        }

        // Return the parsed column and value
        return [
            'column' => $columns,
            'value' => $queryString['value'] ?? '',
        ];
    }
}
