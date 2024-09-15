<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchParser implements SearchParserInterface
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
                'value' => '',
            ];
        }
        $filterable = method_exists($query->getModel(), 'allowQueryParsers')
            ? $query->getModel()->allowQueryParsers()['search']
            : [];

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
