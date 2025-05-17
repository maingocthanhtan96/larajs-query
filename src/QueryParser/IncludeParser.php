<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class IncludeParser
{
    /**
     * Cached FilterParser instance
     *
     * @var FilterParser|null
     */
    private ?FilterParser $filterParser = null;

    /**
     * Cached array of aggregate methods that use '*'
     *
     * @var array<string, bool>
     */
    private static array $starAggregates = ['count' => true, 'exists' => true];

    /**
     * @param  array{with: array, filterWith: array}  $aggregates
     * @return array
     */
    public function parse(array $aggregates): array
    {
        // Initialize FilterParser if not already set
        if ($this->filterParser === null) {
            $this->filterParser = new FilterParser;
        }

        $fxWith = array_map(fn($aggregate) => $this->parseAggregate($aggregate), $aggregates['with'] ?? []);
        $fxWithWhereHas = $this->filterParser->parse($aggregates['filterWith'] ?? []);

        return array_merge($this->mergeWithFx($fxWith), $fxWithWhereHas);
    }

    private function parseAggregate(string $aggregate): array
    {
        // Fast path for simple aggregates without pipe
        if (!str_contains($aggregate, '|')) {
            return [
                'fx' => Method::WITH->value,
                'isNested' => false,
                'parameters' => [$aggregate],
            ];
        }

        // Process aggregates with pipe
        [$relationColumn, $method] = explode('|', $aggregate, 2);
        $method = strtolower($method);

        // Check if relation contains a dot
        if (str_contains($relationColumn, '.')) {
            [$relation, $column] = explode('.', $relationColumn, 2);
        } else {
            $relation = $relationColumn;
            $column = null;
        }

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
        // Early return for empty arrays
        if (!$parsedArray) {
            return [];
        }

        // Separate 'with' items from other items
        $withParameters = [];
        $otherItems = [];

        // Process each item once
        foreach ($parsedArray as $item) {
            if ($item['fx'] === 'with') {
                // Collect all 'with' parameters
                $withParameters = array_merge($withParameters, $item['parameters']);
            } else {
                $otherItems[] = $item;
            }
        }

        // Create a result array with 'with' item first if it exists
        $result = [];
        if ($withParameters) {
            $result[] = [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => $withParameters,
            ];
        }

        // Add other items
        foreach ($otherItems as $item) {
            $result[] = $item;
        }

        return $result;
    }
}
