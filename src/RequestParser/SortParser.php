<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Str;

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
