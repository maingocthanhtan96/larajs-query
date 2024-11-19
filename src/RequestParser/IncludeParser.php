<?php

namespace LaraJS\Query\RequestParser;

class IncludeParser
{
    /**
     * Summary of parse
     *
     * @param  array  $queryString
     * @param  ?array<string>  $filterable
     * @return array
     */
    public function parse(array $queryString, ?array $filterable): array
    {
        $parsedArray = [];
        if (!$queryString || (is_array($filterable) && empty($filterable))) {
            return $parsedArray;
        }

        foreach ($queryString as $aggregate) {
            $field = explode('.', explode('|', $aggregate)[0])[0];
            if (!$filterable || in_array($field, $filterable, true)) {
                $parsedArray[] = $aggregate;
            }
        }

        return $parsedArray;
    }
}
