<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Support\Str;

class IncludeParser
{
    public function parse(array $aggregates): array
    {
        $parsedArray = [];
        foreach ($aggregates as $aggregate) {
            if (Str::contains($aggregate, '|')) {
                [$relationColumn, $method] = explode('|', $aggregate);
                [$relation, $column] = explode('.', $relationColumn) + [null, null];
                $method = strtolower($method);
                $parsedArray[] = [
                    'fx' => 'withAggregate',
                    'isNested' => false,
                    'parameters' => [$relation, in_array($method, ['count', 'exists']) ? '*' : $column, $method],
                ];
            } else {
                $parsedArray[] = [
                    'fx' => 'with',
                    'isNested' => false,
                    'parameters' => [$aggregate],
                ];
            }
        }

        return $parsedArray;
    }
}
