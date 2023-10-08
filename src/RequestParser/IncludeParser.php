<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Support\Str;

class IncludeParser implements IncludeParserInterface
{
    public function parse(array $queryString): array
    {
        $parsedArray = [];
        if (!$queryString) {
            return $parsedArray;
        }
        foreach ($queryString as $aggregate) {
            if (Str::contains($aggregate, '|')) {
                [$relationColumn, $method] = explode('|', $aggregate);
                [$relation, $column] = explode('.', $relationColumn) + [null, null];
                $method = strtolower($method);
                $parsedArray[] = [
                    'fx' => 'withAggregate',
                    'parameters' => [$relation, in_array($method, ['count', 'exists']) ? '*' : $column, $method],
                ];
            } else {
                $parsedArray[] = [
                    'fx' => 'with',
                    'parameters' => [$aggregate],
                ];
            }
        }

        return $parsedArray;
    }
}
