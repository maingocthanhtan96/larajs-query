<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Str;

class SearchParser
{
    /**
     * Parse the search query.
     *
     * @param  array  $queryString
     * @param  array<{column: array<string>, value: string}> $filterable
     * @return array<{column: array<string>, value: string}>
     */
    public function parse(array $queryString, array $filterable): array
    {
        // Extract the column from the query string and filter it
        $columns = Str::of($queryString['column'] ?? '')
            ->trim(',')
            ->explode(',')
            ->filter(function ($value) use ($filterable) {
                $field = explode('.', $value)[0];
                if (!$field) {
                    return false;
                }

                if ($filterable) {
                    return in_array($field, $filterable, true);
                }

                return true;
            })
            ->map(fn($value) => trim($value))
            ->values()
            ->all();

        // Return empty column and value if no valid columns are found
        if (!$columns) {
            return [
                'column' => '',
                'value' => '',
            ];
        }

        // Return the parsed column and value
        return [
            'column' => $columns,
            'value' => $queryString['value'] ?? '',
        ];
    }
}
