<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SortParser implements SortParserInterface
{
    public function parse(Builder $query, string $queryString): array
    {
        if (!$queryString) {
            return [];
        }

        $filterable = method_exists($query->getModel(), 'allowQueryParsers')
            ? $query->getModel()->allowQueryParsers()['sort']
            : [];

        return Str::of($queryString)->trim(',')
            ->explode(',')
            ->filter(function ($value) use ($filterable) {
                if ($filterable) {
                    return in_array(ltrim($value, '-'), $filterable, true);
                }

                return true;
            })->map(function ($pair) {
                if (str_starts_with($pair, '-')) {
                    return [substr($pair, 1), 'desc'];
                }

                return [$pair, 'asc'];
            })
            ->all();
    }
}
