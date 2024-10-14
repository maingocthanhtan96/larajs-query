<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Str;

class SearchParser
{
    /**
     * Search parser
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
                'value' => '',
            ];
        }

        $column = Str::of($queryString['column'] ?? '')
            ->trim(',')
            ->explode(',')
            ->filter(function ($value) use ($filterable) {
                $field = explode('.', $value)[0];
                if ($filterable) {
                    return in_array($field, $filterable, true);
                }

                return true;
            })
            ->map(fn ($value) => trim($value))
            ->all();

        return [
            'column' => $column,
            'value' => $queryString['value'] ?? '',
        ];
    }
}
