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
        if (!$queryString) {
            return $filterable ?? [];
        }

        if (is_array($filterable) && empty($filterable)) {
            return [];
        }

        return collect(explode(',', trim($queryString, ',')))
            ->map(fn($value) => trim($value))
            ->filter(function ($value) use ($filterable) {
                if (!$value) {
                    return false;
                }

                if ($filterable && !in_array($value, $filterable, true)) {
                    throw new \InvalidArgumentException("Field '{$value}' is not filterable.");
                }

                return true;
            })
            ->values()
            ->toArray();
    }
}
