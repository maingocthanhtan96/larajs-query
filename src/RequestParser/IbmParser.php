<?php

namespace LaraJS\QueryParser\RequestParser;

use Exception;
use LaraJS\QueryParser\Enum\IbmOperator;
use LaraJS\QueryParser\Enum\IbmValueType;
use LaraJS\QueryParser\Enum\SqlOperator;

class IbmParser implements IbmParserInterface
{
    /**
     * @throws Exception
     */
    public function parse(array $qsFilter): array
    {
        return $this->parseIbmFilter($qsFilter);
    }

    /**
     * @throws Exception
     */
    public function parseIbmFilter(array $qsFilter): array
    {
        $subResults = [];
        foreach ($qsFilter as $expression) {
            try {
                $subResults[] = $this->parseExpression($expression);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        if (count($subResults) > 1) {
            $subResults = array_reduce($subResults, fn ($prev, $current) => ['OR' => [$prev, $current]]);
        } else {
            $subResults = array_pop($subResults);
        }

        return $subResults;
    }

    /**
     * @throws Exception
     */
    public function parseExpression(string $expression)
    {
        $tokens = $this->tokenizeExpression($expression);
        $stack = [];

        foreach (array_reverse($tokens) as $token) {
            $isOperator = in_array($token, array_map(fn(IbmOperator $operator) => $operator->value, IbmOperator::cases()));
            if (!$isOperator) {
                // Token is an operand
                $stack[] = $token;

                continue;
            }
            // Token is an operator
            switch ($token) {
                case IbmOperator::ANY->value:
                    $anyOperands = [];
                    while (is_string(end($stack))) {
                        $anyOperands[] = $this->coerceValue(array_pop($stack));
                    }
                    $this->errorCheck($token, array_slice($anyOperands, 1));
                    $stack[] = [$this->mapOperator($token) => $anyOperands];
                    break;

                case IbmOperator::EQUALS->value:
                    if (isNullString($stack[count($stack) - 2])) {
                        $attributeRef = $this->coerceValue(array_pop($stack), $token);
                        array_pop($stack); // Null - not included in output
                        $stack[] = [$this->mapOperator($token, true) => $attributeRef];
                    } else {
                        $attributeRef = $this->coerceValue(array_pop($stack), $token);
                        $value = $this->coerceValue(array_pop($stack), $token);
                        $this->errorCheck($token, [$value]);
                        $stack[] = [
                            $this->mapOperator($token, $value === null) => [$attributeRef, $value],
                        ];
                    }
                    break;

                case IbmOperator::NOT->value:
                    $stack[] = [$this->mapOperator($token) => array_pop($stack)];
                    break;
                case IbmOperator::HAS->value:
                case IbmOperator::AND->value:
                case IbmOperator::OR->value:
                    $objOperandA = array_pop($stack);
                    $objOperandB = array_pop($stack);
                    $operands = $objOperandB ? [$objOperandA, $objOperandB] : $objOperandA;
                    $stack[] = [$this->mapOperator($token) => $operands];
                    break;
                default:
                    $attributeRef = $this->coerceValue(array_pop($stack), $token);
                    $value = $this->coerceValue(array_pop($stack), $token);
                    $this->errorCheck($token, [$value]);
                    $stack[] = [
                        $this->mapOperator($token, $value === null) => [$attributeRef, $value],
                    ];
                    break;
            }
        }

        return array_pop($stack);
    }

    public function coerceValue($value, $parentOperator = null): bool|int|string|null
    {
        if (isNullString($value)) {
            return null;
        } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            // constant value
            $value = substr($value, 1, -1);
            if (isBooleanString($value)) { return strtolower($value) === 'true'; }
            if (isNumberString($value)) { return (int) $value; }
            if (isDateString($value)) { return $value; }

            return wildCardString($value, $parentOperator); // string
        } else {
            // attribute reference
            return "#$value";
        }
    }

    public function tokenizeExpression(string $expression): array
    {
        $delimiters = ['(', ')', ','];
        $tokens = [$expression];

        foreach ($delimiters as $delimiter) {
            $tokens = array_reduce($tokens, fn ($carry, $token) => array_merge(
                $carry,
                array_map('trim', explode($delimiter, $token))
            ), []);
        }

        return array_values(array_filter($tokens));
    }

    public function mapOperator($operator, $valueIsNull = false) {
        $operatorMappings = [
            IbmOperator::EQUALS->value => $valueIsNull ? SqlOperator::IS_NULL->value : SqlOperator::EQUALS->value,
            IbmOperator::GREATER_THAN->value => SqlOperator::GREATER_THAN->value,
            IbmOperator::GREATER_OR_EQUAL->value => SqlOperator::GREATER_OR_EQUAL->value,
            IbmOperator::LESS_THAN->value => SqlOperator::LESS_THAN->value,
            IbmOperator::LESS_OR_EQUAL->value => SqlOperator::LESS_OR_EQUAL->value,
            IbmOperator::CONTAINS->value => SqlOperator::LIKE->value,
            IbmOperator::STARTS_WITH->value => SqlOperator::LIKE->value,
            IbmOperator::ENDS_WITH->value => SqlOperator::LIKE->value,
            IbmOperator::ANY->value => SqlOperator::IN->value,
            IbmOperator::NOT->value => SqlOperator::NOT->value,
            IbmOperator::AND->value => SqlOperator::AND->value,
            IbmOperator::OR->value => SqlOperator::OR->value,
            IbmOperator::HAS->value => SqlOperator::HAS->value,
        ];

        return $operatorMappings[$operator];
    }

    /**
     * @throws Exception
     */
    public function errorCheck($operator, $operands): void
    {
        // Blacklist of invalid value types per operator
        $invalidTypeMap = [
            IbmOperator::GREATER_THAN->value => [IbmValueType::NULL],
            IbmOperator::GREATER_OR_EQUAL->value => [IbmValueType::NULL],
            IbmOperator::LESS_THAN->value => [IbmValueType::NULL],
            IbmOperator::LESS_OR_EQUAL->value => [IbmValueType::NULL],
            IbmOperator::CONTAINS->value => [
                IbmValueType::NUMBER,
                IbmValueType::DATE,
                IbmValueType::ATTRIBUTE_REF,
                IbmValueType::NULL,
            ],
            IbmOperator::STARTS_WITH->value => [
                IbmValueType::NUMBER,
                IbmValueType::DATE,
                IbmValueType::ATTRIBUTE_REF,
                IbmValueType::NULL,
            ],
            IbmOperator::ENDS_WITH->value => [
                IbmValueType::NUMBER,
                IbmValueType::DATE,
                IbmValueType::ATTRIBUTE_REF,
                IbmValueType::NULL,
            ],
            IbmOperator::ANY->value => [IbmValueType::ATTRIBUTE_REF],
        ];

        // Throw error for any invalid operator / value type combos
        if (array_key_exists($operator, $invalidTypeMap)) {
            foreach ($invalidTypeMap[$operator] as $valueType) {
                foreach ($operands as $operand) {
                    if ($this->typeOfValue($operand) === $valueType) {
                        throw new Exception(
                            '"' . $operator . '" operator should not be used with ' . $valueType . ' value'
                        );
                    }
                }
            }
        }

        // Throw error if "ANY" operator has multiple types
        if ($operator === IbmOperator::ANY->value) {
            $valueTypes = array_map(fn ($operand) => $this->typeOfValue($operand), $operands);
            $valueTypes = array_filter($valueTypes, function ($v) {
                return $v !== IbmValueType::NULL;
            });

            $hasMultipleTypes = count($valueTypes) > 0 && count(array_unique($valueTypes)) !== 1;

            if ($hasMultipleTypes) {
                throw new Exception('"any" operator should not be used with multiple value types');
            }
        }
    }

    public function typeOfValue($value): ?string
    {
        if ($value === null) {
            return IbmValueType::NULL->name;
        }
        if (is_numeric($value)) {
            return IbmValueType::NUMBER->name;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return IbmValueType::DATE->name;
        }
        if (is_string($value)) {
            if ($value[0] === '#') {
                return IbmValueType::ATTRIBUTE_REF->name;
            } else {
                return IbmValueType::STRING->name;
            }
        }

        // Return null for unsupported types
        return null;
    }
}
