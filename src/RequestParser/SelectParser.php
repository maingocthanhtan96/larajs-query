<?php

namespace LaraJS\Query\RequestParser;

class SelectParser
{
    /**
     * Field parser
     *
     * @param  string  $queryString
     * @param  ?array<string>  $filterable
     * @return array
     */
    public function parse(string $queryString, ?array $filterable): array
    {
        // Return filterable array if query string is empty
        if (!$queryString) {
            return $filterable ?? [];
        }

        // Return empty array if filterable is empty
        if (is_array($filterable) && empty($filterable)) {
            return [];
        }

        // Trim trailing commas and split by comma
        $fields = explode(',', trim($queryString, ','));
        $result = [];

        // Process each field
        foreach ($fields as $field) {
            // Trim whitespace
            $field = trim($field);

            // Skip empty fields
            if ($field === '') {
                continue;
            }

            // Check if field is allowed
            if ($filterable && !in_array($field, $filterable, true)) {
                throw new \InvalidArgumentException("Field '{$field}' is not filterable.");
            }

            $result[] = $field;
        }

        return $result;
    }
}
