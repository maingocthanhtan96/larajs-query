<?php

namespace LaraJS\Query\RequestParser;

use InvalidArgumentException;
use LaraJS\Query\Enum\SqlOperator;

class IncludeParser
{
    /**
     * Cache for filterable maps to avoid rebuilding for the same filterable array
     *
     * @var array<string, array<string, string>>
     */
    private static array $filterableMapCache = [];

    /**
     * List of valid aggregate functions
     *
     * @var array<string>
     */
    private static array $validAggregates = ['count', 'sum', 'avg', 'min', 'max', 'exists'];

    /**
     * Summary of parse
     *
     * @param  array  $queryString
     * @param  ?array<string>  $filterable
     * @return array{with: array, withWhereHas: array}
     */
    public function parse(array $queryString, ?array $filterable): array
    {
        $includes = [
            'with' => [],
            'filterWith' => [],
        ];

        // Early return if query string is empty
        if (empty($queryString)) {
            return $includes;
        }

        // Get filterable map from cache or build it
        $filterableMap = $this->getFilterableMap($filterable ?? []);

        // Process each aggregate
        foreach ($queryString as $aggregate) {
            // Split relation and args
            $parts = explode('|', $aggregate, 2);
            $relation = $parts[0];
            $args = $parts[1] ?? null;

            // Get relation map for filtering
            $relationParts = explode(':', $relation, 2);
            $relationMap = $relationParts[0];

            // Check if relation is allowed
            if (!isset($filterableMap[$relationMap]) && !is_null($filterable)) {
                throw new InvalidArgumentException("Relations: '{$relation}'is not allowed");
            }

            // Process args if present and not an aggregate function
            if ($args && !in_array(strtolower($args), self::$validAggregates, true)) {
                $withWhereHas = (new FilterParser)->parse($args, null);

                $includes['filterWith'][] = [
                    SqlOperator::FILTER_RELATION->value => [
                        $relation,
                        $withWhereHas,
                    ],
                ];
            } else {
                $includes['with'][] = $aggregate;
            }
        }

        return $includes;
    }

    /**
     * Get a filterable map from cache or build it
     *
     * @param  array<string>  $filterable
     * @return array<string, string>
     */
    private function getFilterableMap(array $filterable): array
    {
        // Return an empty map if filterable is empty
        if (!$filterable) {
            return [];
        }

        // Generate cache key based on a filterable array
        $cacheKey = md5(json_encode($filterable));

        // Return from cache if available
        if (isset(self::$filterableMapCache[$cacheKey])) {
            return self::$filterableMapCache[$cacheKey];
        }

        // Build and cache the filterable map
        $filterableMap = $this->buildFilterableMap($filterable);

        // Limit cache size to prevent memory issues
        if (count(self::$filterableMapCache) > 100) {
            array_shift(self::$filterableMapCache);
        }

        self::$filterableMapCache[$cacheKey] = $filterableMap;

        return $filterableMap;
    }

    /**
     * Build a filterable map from the filterable array.
     *
     * @param  array<string>  $filterable
     * @return array<string, string>
     */
    private function buildFilterableMap(array $filterable): array
    {
        $filterableMap = [];
        foreach ($filterable as $item) {
            // Extract relation name without columns or aggregates
            $parts = explode(':', $item, 2);
            $relation = $parts[0];

            $parts = explode('|', $relation, 2);
            $relation = $parts[0];

            if ($relation) {
                $filterableMap[$relation] = $item;
            }
        }

        return $filterableMap;
    }
}
