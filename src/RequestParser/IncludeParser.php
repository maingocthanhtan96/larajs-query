<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;

class IncludeParser implements IncludeParserInterface
{
    public function parse(Builder $query, array $queryString): array
    {
        $parsedArray = [];
        if (!$queryString) {
            return $parsedArray;
        }
        $filterable = method_exists($query->getModel(), 'allowQueryParsers')
            ? $query->getModel()->allowQueryParsers()['include']
            : [];

        foreach ($queryString as $aggregate) {
            $field = explode('.', explode('|', $aggregate)[0])[0];
            if (!$filterable || in_array($field, $filterable, true)) {
                $parsedArray[] = $aggregate;
            }
        }

        return $parsedArray;
    }
}
