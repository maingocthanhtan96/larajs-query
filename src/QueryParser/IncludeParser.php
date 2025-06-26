<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class IncludeParser
{
    private ?FilterParser $filterParser = null;

    private static array $starAggregates = ['count' => true, 'exists' => true];

    public function parse(array $aggregates): array
    {
        $this->filterParser ??= new FilterParser;

        $fxWith = array_map(fn($aggregate) => $this->parseAggregate($aggregate), $aggregates['with'] ?? []);
        $fxWithWhereHas = $this->filterParser->parse($aggregates['filterWith'] ?? []);

        return array_merge($this->mergeWithFx($fxWith), $fxWithWhereHas);
    }

    private function parseAggregate(string $aggregate): array
    {
        if (!str_contains($aggregate, '|')) {
            return [
                'fx' => Method::WITH->value,
                'isNested' => false,
                'parameters' => [$aggregate],
            ];
        }

        [$relationColumn, $method] = explode('|', $aggregate, 2);
        $method = strtolower($method);
        [$relation, $column] = str_contains($relationColumn, '.')
            ? explode('.', $relationColumn, 2)
            : [$relationColumn, null];

        return [
            'fx' => Method::WITH_AGGREGATE->value,
            'isNested' => false,
            'parameters' => [
                $relation,
                isset(self::$starAggregates[$method]) ? '*' : $column,
                $method,
            ],
        ];
    }

    private function mergeWithFx(array $parsedArray): array
    {
        if (!$parsedArray) {
            return [];
        }

        $withParameters = [];
        $otherItems = [];

        foreach ($parsedArray as $item) {
            if ($item['fx'] === 'with') {
                array_push($withParameters, ...$item['parameters']);
            } else {
                $otherItems[] = $item;
            }
        }

        $result = [];
        if ($withParameters) {
            $result[] = [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => $withParameters,
            ];
        }

        return array_merge($result, $otherItems);
    }
}
