<?php

namespace LaraJS\Query\RequestParser;

use InvalidArgumentException;
use LaraJS\Query\Enum\SqlOperator;

class IncludeParser
{
    private array $validAggregates = ['count', 'sum', 'avg', 'min', 'max', 'exists'];

    private ?FilterParser $filterParser = null;

    public function parse(array $queryString, ?array $filterable): array
    {
        $includes = ['with' => [], 'filterWith' => []];

        if (empty($queryString)) {
            return $includes;
        }

        $filterableMap = $this->getFilterableMap($filterable ?? []);

        foreach ($queryString as $aggregate) {
            [$relation, $args] = explode('|', $aggregate, 2) + [null, null];
            $relationMap = strstr($relation, ':', true) ?: $relation;

            if ($filterable !== null && !isset($filterableMap[$relationMap])) {
                throw new InvalidArgumentException("Relations: '{$relation}' is not allowed");
            }

            if ($args && !in_array(strtolower($args), $this->validAggregates, true)) {
                $this->filterParser ??= new FilterParser;
                $includes['filterWith'][] = [
                    SqlOperator::FILTER_RELATION->value => [
                        $relation,
                        $this->filterParser->parse($args, null),
                    ],
                ];
            } else {
                $includes['with'][] = $aggregate;
            }
        }

        return $includes;
    }

    private function getFilterableMap(array $filterable): array
    {
        $filterableMap = [];
        foreach ($filterable as $item) {
            $relation = strtok($item, ':|');
            if ($relation) {
                $filterableMap[$relation] = $item;
            }
        }

        return $filterableMap;
    }
}
