<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;

class DateParser implements SearchParserInterface
{
    /**
     * @param  Builder  $query
     * @param  array  $queryString
     * @return array
     */
    public function parse(Builder $query, array $queryString): array
    {
        if (!$queryString) {
            return [
                'column' => '',
                'value' => [],
            ];
        }
        $filterable = method_exists($query->getModel(), 'allowQueryParsers')
            ? $query->getModel()->allowQueryParsers()['date']
            : [];
        $column = $queryString['column'] ?? '';
        if ($filterable && !in_array($column, $filterable, true)) {
            return [];
        }

        return [
            'column' => $column,
            'value' => $queryString['value'] ?? [],
        ];
    }
}
