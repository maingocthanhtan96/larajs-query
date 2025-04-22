<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Str;

class SearchParser
{
    /**
     * Parse the search query.
     *
     * @param  array  $queryString
     * @param  array  $defaultData
     * @param  ?array{column: array<string>, value: string}  $filterable
     * @return array{column: array<string>, value: string}
     */
    public function parse(array $queryString, array $defaultData, ?array $filterable): array
    {
        if (is_array($filterable) && empty($filterable)) {
            return $defaultData;
        }
        // Extract the column from the query string and filter it
        $columns = Str::of($queryString['column'] ?? '')
            ->trim(',')
            ->explode(',')
            ->filter(function ($value) use ($filterable) {
                $field = explode('.', $value)[0] ?? null;

                return $field && (!$filterable || in_array($field, $filterable, true));
            })
            ->map(fn($value) => trim($value))
            ->values()
            ->all();

        // Return an empty column and value if no valid columns are found
        if (!$columns) {
            return $defaultData;
        }

        // Return the parsed column and value
        return [
            'column' => $columns,
            'value' => $queryString['value'] ?? $defaultData['value'],
        ];
    }
}
