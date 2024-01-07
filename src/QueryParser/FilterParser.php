<?php

namespace LaraJS\QueryParser\QueryParser;

use LaraJS\QueryParser\Enum\Method;
use LaraJS\QueryParser\Enum\Operator;
use Illuminate\Support\Arr;

class FilterParser implements FilterParserInterface
{
    public function parse(array $filters, bool $isOr = false): array
    {
        $parsedArray = [];
        foreach ($filters as $operator => $filter) {
            $isNested = in_array($operator, [Operator::AND->value, Operator::OR->value, Operator::NOT->value]);
            $parameters = $isNested
                ? $this->sortNestedFilters($filter, $operator === Operator::OR->value)
                : $this->parseParametersForObjection($operator, $filter, $isOr);
            $parsedArray[] = [
                'fx' => $isOr ? convertToOrFormat($this->getMethod($operator)) : $this->getMethod($operator),
                'isNested' => $isNested,
                'parameters' => $parameters,
            ];
        }

        return $parsedArray;
    }

    //To reconstruct the parameters to objections format
    public function parseParametersForObjection($operator, $value, $isOr): array
    {
        $specialOperators = [Operator::IN->value, Operator::NOT_IN->value, Operator::NOT->value, Operator::IS_NULL->value, Operator::IS_NOT_NULL->value, Operator::RELATION->value];
        $operatorMap = [
            Operator::HAS->value => Operator::GREATER_OR_EQUAL->value,
        ];
        $isSpecialOperator = in_array(strtolower($operator), array_map('strtolower', $specialOperators), true);
        $operator = $operatorMap[$operator] ?? $operator;
        if (is_array($value)) {
            $sequelizeKey = removeHashFromString($value[0]);
            if ($isSpecialOperator) {
                // HANDLE IN AND NOT IN
                if ($operator === Operator::RELATION->value) {
                    $sequelizeKeyField = removeHashFromString($value[1]);
                    $parameters = [$sequelizeKey, $sequelizeKeyField, ...array_slice($value, 2)];
                } else {
                    $parameters = [$sequelizeKey, array_slice($value, 1)];
                }
            } else {
                $sequelizeValue = count($value) > 2 ? array_slice($value, 1) : $value[1];
                $parameters = [$sequelizeKey, $operator, $sequelizeValue];
            }
        } else {
            // HANDLE NULL AND NOT NULL
            $sequelizeKey = removeHashFromString($value);
            $parameters = [$sequelizeKey];
        }

        return $parameters;
    }

    //To handle "OR" AND "AND" recursively
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
            Operator::RELATION->value
                => Method::fromName($key)->value,
            default => Method::DEFAULT->value,
        };
    }
}
