<?php

namespace LaraJS\QueryParser\QueryParser;

use LaraJS\QueryParser\Enum\Method;
use LaraJS\QueryParser\Enum\Operator;

class FilterParser implements FilterParserInterface
{
    public function parse(array $filters, bool $isOr = false): array
    {
        $parsedArray = [];
        foreach (array_keys($filters) as $key) {
            if (in_array($key, [Operator::AND->value, Operator::OR->value, Operator::NOT->value]) || ($key === Operator::HAS->value && is_array($filters[$key]))) {
                $parameters = $this->sortNestedFilters($filters[$key], $key === Operator::OR->value);
                $fx =$this->getMethod($key);
                $parsedArray[] = [
                    'fx' => $isOr ? convertToOrFormat($fx) : $fx,
                    'isNested' => true,
                    'parameters' => $parameters,
                ];
            } else {
                $parsed = $this->parseParametersForObjection($key, $filters[$key], $isOr);
                $parsedArray[] = [
                    'fx' => $parsed['fx'],
                    'isNested' => false,
                    'parameters' => $parsed['parameters'],
                ];
            }
        }

        return $parsedArray;
    }

    //To reconstruct the parameters to objections format
    public function parseParametersForObjection($operator, $value, $isOr): array
    {
        $specialOperators = [
            Operator::IN->value,
            Operator::NOT_IN->value,
            Operator::NOT->value,
            Operator::IS_NULL->value,
            Operator::IS_NOT_NULL->value,
            Operator::HAS->value,
        ];

        $isSpecialOperator = in_array(strtolower($operator), array_map('strtolower', $specialOperators), true);

        $sequelizeOperator = $operator;
        if (is_array($value)) {
            $sequelizeKey = removeHashFromString($value[0]);

            if ($isSpecialOperator) {
                // HANDLE IN AND NOT IN
                $fx = Method::fromName($operator)->value;
                $sequelizeValue = array_slice($value, 1);
                if ($fx === Method::HAS->value) {
                    $value = array_pop($sequelizeValue);
                    $key = key($value);
                    $parameters = [$sequelizeKey, [$this->parseParametersForObjection($key, $value[$key], $isOr)]];
                } else {
                    $parameters = [$sequelizeKey, $sequelizeValue];
                }
            } else {
                $fx = Method::DEFAULT->value;
                $sequelizeValue = count($value) > 2 ? array_slice($value, 1) : $value[1];
                $parameters = [$sequelizeKey, $sequelizeOperator, $sequelizeValue];
            }
        } else {
            // HANDLE NULL AND NOT NULL
            $sequelizeKey = removeHashFromString($value);
            $fx = Method::fromName($operator)->value;
            $parameters = [$sequelizeKey];
        }

        return [
            'fx' => $fx,
            'parameters' => $parameters,
        ];
    }

    //To handle "OR" AND "AND" recursively
    public function sortNestedFilters($filters, $isOr = false): array
    {
        $parsedArray = [];
        $filters = \Arr::isAssoc($filters) ? [$filters] : $filters;
        foreach ($filters as $i => $filter) {
            // Use the "orWhere" only from the second iteration.
            $useOr = $isOr && $i > 0;
            $filter = $filter ?? [];
            $parseFilterResponse = is_array($filter) ? $this->parse($filter, $useOr) : [$filter];
            $parsedArray = [...$parsedArray, ...$parseFilterResponse];
        }

        return $parsedArray;
    }

    public function getMethod(string $key)
    {
        return match ($key) {
            Operator::NOT->value, Operator::HAS->value => Method::fromName($key)->value,
            default => Method::DEFAULT->value,
        };
    }
}
