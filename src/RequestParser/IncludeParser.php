<?php

namespace LaraJS\Query\RequestParser;

use InvalidArgumentException;
use LaraJS\Query\Enum\IbmOperator;

class IncludeParser
{
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
            'withWhereHas' => [],
        ];

        if (!$queryString) {
            return $includes;
        }

        $filterableMap = $this->buildFilterableMap($filterable ?? []);

        foreach ($queryString as $aggregate) {
            [$relation, $args] = explode('|', $aggregate, 2) + [null, null];
            [$relationMap] = explode(':', $relation, 2) + [null, null];

            if (!isset($filterableMap[$relationMap]) && !is_null($filterable)) {
                throw new InvalidArgumentException("Relations: '{$relation}'is not allowed");
            }
            if ($args && !in_array(strtolower($args), ['count', 'sum', 'avg', 'min', 'max', 'exists'], true)) {
                $withWhereHas = (new FilterParser)->parse(IbmOperator::INCLUDE_RELATION->value . "($relation, $args)", null);
                $includes['withWhereHas'][] = $withWhereHas;
            } else {
                $includes['with'][] = $aggregate;
            }
        }

        return $includes;
    }

    /**
     * Build a filterable map from the filterable array.
     *
     * @param  array<string>  $filterable
     * @return array<string, array<string>>
     */
    private function buildFilterableMap(array $filterable): array
    {
        $filterableMap = [];
        foreach ($filterable as $item) {
            [$relation] = explode(':', $item, 2);
            [$relation] = explode('|', $relation, 2);
            if ($relation) {
                $filterableMap[$relation] = $item;
            }
        }

        return $filterableMap;
    }
}
