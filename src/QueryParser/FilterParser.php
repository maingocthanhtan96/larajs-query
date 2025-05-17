<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Support\Arr;
use LaraJS\Query\Enum\Method;
use LaraJS\Query\Enum\Operator;

class FilterParser
{
    /**
     * Cache for special operators in lowercase
     *
     * @var array<string>
     */
    private array $specialOperatorsLower;

    /**
     * Cache for relation operators
     *
     * @var array<string, bool>
     */
    private array $relationOperatorsMap;

    /**
     * Cache for operator mappings
     *
     * @var array<string, string>
     */
    private array $operatorMap;

    /**
     * Cache for direct method mappings
     *
     * @var array<string, bool>
     */
    private array $directMappingsMap;

    /**
     * Cache for nested operators
     *
     * @var array<string, bool>
     */
    private array $nestedOperatorsMap;

    public function __construct()
    {
        // Initialize special operators
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
            Operator::BETWEEN->value,
            Operator::BETWEEN_RELATION->value,
        ];
        $this->specialOperatorsLower = array_map('strtolower', $specialOperators);

        // Initialize relation operators as a map for faster lookups
        $relationOperators = [Operator::RELATION->value, Operator::ANY_RELATION->value, Operator::BETWEEN_RELATION->value];
        $this->relationOperatorsMap = array_fill_keys($relationOperators, true);

        // Initialize operator map
        $this->operatorMap = [
            Operator::HAS->value => Operator::GREATER_OR_EQUAL->value,
        ];

        // Initialize direct mappings as a map for faster lookups
        $directMappings = [
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
        ];
        $this->directMappingsMap = array_fill_keys($directMappings, true);

        // Initialize nested operators as a map for faster lookups
        $nestedOperators = [
            Operator::AND->value,
            Operator::OR->value,
            Operator::NOT->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::FILTER_RELATION->value,
        ];
        $this->nestedOperatorsMap = array_fill_keys($nestedOperators, true);
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

    /**
     * Handle "OR" AND "AND" recursively
     * Optimized to avoid unnecessary array operations
     */
    public function sortNestedFilters($filters, $isOr = false): array
    {
        // Ensure filters is an array of arrays
        $filters = Arr::isAssoc($filters) ? [$filters] : $filters;

        // Pre-allocate array with approximate size to avoid resizing
        $parsedArray = [];
        $count = count($filters);

        for ($i = 0; $i < $count; $i++) {
            // Use the "orWhere" only from the second iteration
            $useOr = $isOr && $i > 0;

            // Handle null filters
            $filter = $filters[$i] ?? [];

            // Parse the filter and add to results
            if (is_array($filter)) {
                $parseFilterResponse = $this->parse($filter, $useOr);
                foreach ($parseFilterResponse as $response) {
                    $parsedArray[] = $response;
                }
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

    /**
     * Convert a method name to its "or" format (e.g., "where" to "orWhere")
     * Using a more direct approach for string concatenation
     */
    private function convertToOrFormat($str): string
    {
        return 'or' . ucfirst($str);
    }

    /**
     * Remove hash character from string
     * Using a more efficient approach for simple character removal
     */
    private function removeHashFromString($str): string
    {
        // For non-string values, just return as is
        if (!is_string($str)) {
            return $str;
        }

        // Check if the string contains a hash to avoid unnecessary replacement
        return $str[0] === '#' ? substr($str, 1) : $str;
    }

    // Check if an operator is a nested operator
    private function isNested(string $op): bool
    {
        return in_array($op, [
            Operator::AND->value,
            Operator::OR->value,
            Operator::NOT->value,
            Operator::FILTER_RELATION_HAS->value,
            Operator::FILTER_RELATION->value,
        ], true);
    }
}
