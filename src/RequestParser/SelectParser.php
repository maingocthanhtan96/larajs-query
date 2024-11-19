<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Str;

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
        if (!$queryString || (is_array($filterable) && empty($filterable))) {
            return [];
        }

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
