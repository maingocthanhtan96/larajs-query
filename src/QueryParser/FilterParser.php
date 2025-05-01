<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Support\Arr;
use LaraJS\Query\Enum\Method;
use LaraJS\Query\Enum\Operator;

class FilterParser
{
    public function parse(array $filters, bool $isOr = false): array
    {
        $parsedArray = [];
        $isNested = static fn($op) => in_array($op, [
            Operator::AND->value,
            Operator::OR->value,
            Operator::NOT->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::FILTER_RELATION->value,
        ], true);

        $parseFilter = function ($operator, $filter) use ($isNested, $isOr) {
            $nested = $isNested($operator);
            $parameters = $nested
                ? $this->sortNestedFilters($filter, $operator === Operator::OR->value)
                : $this->parseParametersForObjection($operator, $filter);

            return [
                'fx' => $isOr ? $this->convertToOrFormat($this->getMethod($operator)) : $this->getMethod($operator),
                'isNested' => $nested,
                'parameters' => $parameters,
            ];
        };
        $filters = Arr::isAssoc($filters) ? [$filters] : $filters;

        foreach ($filters as $filterNotAssoc) {
            foreach ($filterNotAssoc as $operator => $filter) {
                $parsedArray[] = $parseFilter($operator, $filter);
            }
        }

        return $parsedArray;
    }

    // To reconstruct the parameters to objections format
    public function parseParametersForObjection($operator, $value): array
    {
        $specialOperators = [
            Operator::IN->value,
            Operator::NOT_IN->value,
            Operator::NOT->value,
            Operator::IS_NULL->value,
            Operator::IS_NOT_NULL->value,
            Operator::RELATION->value,
            Operator::ANY_RELATION->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::FILTER_RELATION->value,
        ];
        $operatorMap = [
            Operator::HAS->value => Operator::GREATER_OR_EQUAL->value,
        ];
        $isSpecialOperator = in_array(strtolower($operator), array_map('strtolower', $specialOperators), true);
        $operator = $operatorMap[$operator] ?? $operator;
        if (is_array($value)) {
            $sequelizeKey = $this->removeHashFromString($value[0]);
            if ($isSpecialOperator) {
                // HANDLE IN AND NOT IN
                $sequelizeKeyField = $this->removeHashFromString($value[1]);
                if (in_array($operator, [Operator::RELATION->value, Operator::ANY_RELATION->value], true)) {
                    $sliceIndex = $operator === Operator::RELATION->value ? 2 : 3;
                    $parameters = [$sequelizeKey, $sequelizeKeyField, ...array_slice($value, $sliceIndex)];
                } else {
                    $parameters = [$sequelizeKey, array_slice($value, 1)];
                }
            } else {
                $sequelizeValue = count($value) > 2 ? array_slice($value, 1) : $value[1];
                $parameters = [$sequelizeKey, $operator, $sequelizeValue];
            }
        } else {
            // HANDLE NULL AND NOT NULL
            $sequelizeKey = $this->removeHashFromString($value);
            $parameters = [$sequelizeKey];
        }

        return $parameters;
    }

    // To handle "OR" AND "AND" recursively
    public function sortNestedFilters($filters, $isOr = false): array
    {
        $parsedArray = [];
        $filters = Arr::isAssoc($filters) ? [$filters] : $filters;
        foreach ($filters as $i => $filter) {
            // Use the "orWhere" only from the second iteration.
            $useOr = $isOr && $i > 0;
            $filter = $filter ?? [];
            $parseFilterResponse = is_array($filter) ? $this->parse($filter, $useOr) : [$filter];
            $parsedArray = [...$parsedArray, ...$parseFilterResponse];
        }

        return $parsedArray;
    }

    public function getMethod(string $key): string
    {
        return match ($key) {
            Operator::HAS->value,
            Operator::NOT->value,
            Operator::NOT_IN->value,
            Operator::IN->value,
            Operator::IS_NULL->value,
            Operator::IS_NOT_NULL->value,
            Operator::RELATION->value,
            Operator::ANY_RELATION->value,
            Operator::FILTER_RELATION_HAS->value => Method::fromName($key)->value,
            Operator::FILTER_RELATION->value => Method::WITH->value,
            default => Method::DEFAULT->value,
        };
    }

    private function convertToOrFormat($str): string
    {
        $capStr = ucfirst($str);

        return "or$capStr";
    }

    private function removeHashFromString($str): string
    {
        return str_replace('#', '', $str);
    }
}
