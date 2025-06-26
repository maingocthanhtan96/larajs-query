<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Support\Arr;
use LaraJS\Query\Enum\Method;
use LaraJS\Query\Enum\Operator;

class FilterParser
{
    private array $specialOperatorsLower;

    private array $relationOperatorsMap;

    private array $operatorMap;

    private array $directMappingsMap;

    private array $nestedOperatorsMap;

    public function __construct()
    {
        $this->specialOperatorsLower = array_map('strtolower', [
            Operator::IN->value,
            Operator::NOT_IN->value,
            Operator::NOT->value,
            Operator::IS_NULL->value,
            Operator::IS_NOT_NULL->value,
            Operator::RELATION->value,
            Operator::ANY_RELATION->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::FILTER_RELATION->value,
            Operator::BETWEEN->value,
            Operator::BETWEEN_RELATION->value,
        ]);

        $this->relationOperatorsMap = array_fill_keys([
            Operator::RELATION->value,
            Operator::ANY_RELATION->value,
            Operator::BETWEEN_RELATION->value,
        ], true);

        $this->operatorMap = [Operator::HAS->value => Operator::GREATER_OR_EQUAL->value];

        $this->directMappingsMap = array_fill_keys([
            Operator::HAS->value,
            Operator::NOT->value,
            Operator::NOT_IN->value,
            Operator::IN->value,
            Operator::IS_NULL->value,
            Operator::IS_NOT_NULL->value,
            Operator::RELATION->value,
            Operator::ANY_RELATION->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::BETWEEN->value,
            Operator::BETWEEN_RELATION->value,
        ], true);

        $this->nestedOperatorsMap = array_fill_keys([
            Operator::AND->value,
            Operator::OR->value,
            Operator::NOT->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::FILTER_RELATION->value,
        ], true);
    }

    public function parse(array $filters, bool $isOr = false): array
    {
        $parsedArray = [];

        $parseFilter = function ($operator, $filter) use ($isOr) {
            $nested = $this->isNested($operator);
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

    public function parseParametersForObjection($operator, $value): array
    {
        $isSpecialOperator = in_array(strtolower($operator), $this->specialOperatorsLower, true);
        $operator = $this->operatorMap[$operator] ?? $operator;

        if (is_array($value)) {
            $sequelizeKey = $this->removeHashFromString($value[0]);
            if ($isSpecialOperator) {
                // HANDLE IN AND NOT IN
                if (isset($this->relationOperatorsMap[$operator])) {
                    $sequelizeKeyField = $this->removeHashFromString($value[1]);
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
            $sequelizeKey = $this->removeHashFromString($value);
            $parameters = [$sequelizeKey];
        }

        return $parameters;
    }

    public function sortNestedFilters($filters, $isOr = false): array
    {
        $filters = Arr::isAssoc($filters) ? [$filters] : $filters;
        $parsedArray = [];

        foreach ($filters as $i => $filter) {
            $useOr = $isOr && $i > 0;

            if (is_array($filter)) {
                array_push($parsedArray, ...$this->parse($filter, $useOr));
            } else {
                $parsedArray[] = $filter;
            }
        }

        return $parsedArray;
    }

    public function getMethod(string $key): string
    {
        if (isset($this->directMappingsMap[$key])) {
            return Method::fromName($key)->value;
        }

        if ($key === Operator::FILTER_RELATION->value) {
            return Method::WITH->value;
        }

        return Method::DEFAULT->value;
    }

    private function convertToOrFormat($str): string
    {
        return 'or' . ucfirst($str);
    }

    private function removeHashFromString($str): string
    {
        return is_string($str) && str_starts_with($str, '#') ? substr($str, 1) : $str;
    }

    private function isNested(string $op): bool
    {
        return isset($this->nestedOperatorsMap[$op]);
    }
}
