<?php

namespace LaraJS\QueryParser\QueryParser;

use Illuminate\Support\Arr;
use LaraJS\QueryParser\Enum\Method;
use LaraJS\QueryParser\Enum\Operator;

class FilterParser implements FilterParserInterface
{
    public function parse(array $filters, bool $isOr = false): array
    {
        $parsedArray = [];
        foreach (array_keys($filters) as $key) {
            if (in_array($key, [Operator::AND->value, Operator::OR->value, Operator::NOT->value])) {
                $parameters = $this->sortNestedFilters($filters[$key], $key === Operator::OR->value);
                $fx = $key === Operator::NOT->value ? Method::fromName(Operator::NOT->value)->value : Method::DEFAULT->value;
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
        ];

        $isSpecialOperator = in_array(strtolower($operator), array_map('strtolower', $specialOperators), true);

        $sequelizeOperator = $operator;
        if (is_array($value)) {
            $sequelizeKey = removeHashFromString($value[0]);

            if ($isSpecialOperator) {
                // HANDLE IN AND NOT IN
                $fx = Method::fromName($operator)->value;
                $sequelizeValue = array_slice($value, 1);
                $parameters = [$sequelizeKey, $sequelizeValue];
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
            'fx' => $isOr ? convertToOrFormat($fx) : $fx,
            'parameters' => $parameters,
        ];
    }

    //To handle "OR" AND "AND" recursively
    public function sortNestedFilters($filters, $isOr = false): array
    {
        $parsedArray = [];
        $errors = [];
        foreach (Arr::wrap($filters) as $i => $filter) {
            // Use the "orWhere" only from the second iteration.
            $useOr = $isOr && $i > 0;
            $parseFilterResponse = $this->parse($filter ?? [], $useOr);
            $parsedArray = [...$parsedArray, ...$parseFilterResponse];
        }

        return $parsedArray;
    }
}
