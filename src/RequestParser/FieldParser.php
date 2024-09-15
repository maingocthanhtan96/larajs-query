<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FieldParser implements FieldParserInterface
{
    /**
     * @param  Builder  $query
     * @param  string  $queryString
     * @return array
     */
    public function parse(Builder $query, string $queryString): array
    {
        if (!$queryString) {
            return [];
        }
        $filterable = method_exists($query->getModel(), 'allowQueryParsers')
            ? $query->getModel()->allowQueryParsers()['field']
            : [];

        return Str::of($queryString)
            ->trim(',')
            ->explode(',')
            ->filter(function ($value) use ($filterable) {
                if ($filterable) {
                    return in_array($value, $filterable, true);
                }

                return true;
            })
            ->map(fn ($value) => trim($value))
            ->all();
    }
}
