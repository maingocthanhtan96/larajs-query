<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Arr;
use LaraJS\Query\Enum\IbmOperator;
use LaraJS\Query\Enum\SqlOperator;

class FilterParser
{
    private ?array $ibmOperatorValues = null;

    private ?array $operatorMappings = null;

    public function parse(string $queryString, ?array $filterable): array
    {
        if (!$queryString || (is_array($filterable) && empty($filterable))) {
            return [];
        }

        return $this->parseFilter(Arr::wrap($queryString), $filterable ?? []);
    }

    public function parseFilter(array $qsFilter, array $filterable): array
    {
        $subResults = [];
        foreach ($qsFilter as $expression) {
            $subResults[] = $this->parseExpression($expression, $filterable);
        }
        if (count($subResults) > 1) {
            $subResults = array_reduce($subResults, fn($prev, $current) => ['OR' => [$prev, $current]]);
        } else {
            $subResults = array_pop($subResults);
        }

        return $subResults ?? [];
    }

    public function parseExpression(string $expression, array $filterable): ?array
    {
        $tokens = $this->tokenizeExpression($expression);
        $stack = [];

        $this->ibmOperatorValues ??= array_map(fn(IbmOperator $operator) => $operator->value, IbmOperator::cases());

        foreach (array_reverse($tokens) as $token) {
            $isOperator = in_array($token, $this->ibmOperatorValues, false);
            if (!$isOperator) {
                // Token is an operand
                $stack[] = $token;

                continue;
            }
            // Token is an operator
            switch ($token) {
                case IbmOperator::ANY->value:
                    $anyOperands = array_map(fn($v) => $this->coerceValue($v), array_reverse(array_splice($stack, -count($stack), count($stack), [])));
                    if ($this->checkAllowFilter($anyOperands[0], $filterable)) {
                        $stack[] = [$this->mapOperator($token) => $anyOperands];
                    }
                    break;
                case IbmOperator::ANY_RELATION->value:
                case IbmOperator::EQUALS_RELATION->value:
                case IbmOperator::GREATER_OR_EQUAL_RELATION->value:
                case IbmOperator::GREATER_THAN_RELATION->value:
                case IbmOperator::LESS_OR_EQUAL_RELATION->value:
                case IbmOperator::LESS_THAN_RELATION->value:
                case IbmOperator::CONTAINS_RELATION->value:
                case IbmOperator::STARTS_WITH_RELATION->value:
                case IbmOperator::ENDS_WITH_RELATION->value:
                case IbmOperator::BETWEEN_RELATION->value:
                    $attributeRefRelation = $this->coerceValue(array_pop($stack), $token);
                    $attributeRefField = $this->coerceValue(array_pop($stack), $token);
                    $operator = match ($token) {
                        IbmOperator::ANY_RELATION->value => SqlOperator::IN->value,
                        IbmOperator::EQUALS_RELATION->value => SqlOperator::EQUALS->value,
                        IbmOperator::GREATER_OR_EQUAL_RELATION->value => SqlOperator::GREATER_OR_EQUAL->value,
                        IbmOperator::GREATER_THAN_RELATION->value => SqlOperator::GREATER_THAN->value,
                        IbmOperator::LESS_OR_EQUAL_RELATION->value => SqlOperator::LESS_OR_EQUAL->value,
                        IbmOperator::LESS_THAN_RELATION->value => SqlOperator::LESS_THAN->value,
                        IbmOperator::BETWEEN_RELATION->value => SqlOperator::BETWEEN->value,
                        default => SqlOperator::LIKE->value,
                    };
                    $value = match ($token) {
                        IbmOperator::ANY_RELATION->value => array_map(
                            fn($v) => $this->coerceValue($v),
                            array_reverse(array_splice($stack, -count($stack), count($stack), []))
                        ),
                        IbmOperator::BETWEEN_RELATION->value => [
                            $this->coerceValue(array_pop($stack), $token),
                            $this->coerceValue(array_pop($stack), $token),
                        ],
                        default => $this->coerceValue(array_pop($stack), $token)
                    };

                    if ($this->checkAllowFilter($attributeRefRelation, $filterable)) {
                        $mapOperator = $this->mapOperator($token, !is_array($value) && $value === null);
                        $params = [$attributeRefRelation, $attributeRefField];

                        if (!in_array($token, [IbmOperator::BETWEEN_RELATION->value, IbmOperator::ANY_RELATION->value])) {
                            $params[] = $operator;
                        }
                        $params[] = $value;

                        $stack[] = [$mapOperator => $params];
                    }

                    break;

                case IbmOperator::EQUALS->value:
                    if ($stack[count($stack) - 2] === 'null') {
                        $attributeRef = $this->coerceValue(array_pop($stack), $token);
                        array_pop($stack); // Null - not included in output
                        if ($this->checkAllowFilter($attributeRef, $filterable)) {
                            $stack[] = [$this->mapOperator($token, true) => $attributeRef];
                        }
                    } else {
                        $attributeRef = $this->coerceValue(array_pop($stack), $token);
                        $value = $this->coerceValue(array_pop($stack), $token);
                        if ($this->checkAllowFilter($attributeRef, $filterable)) {
                            $stack[] = [
                                $this->mapOperator($token, $value === null) => [$attributeRef, $value],
                            ];
                        }
                    }
                    break;
                case IbmOperator::BETWEEN->value:
                    $attributeRef = $this->coerceValue(array_pop($stack), $token);
                    $firstBound = $this->coerceValue(array_pop($stack), $token);
                    $secondBound = $this->coerceValue(array_pop($stack), $token);
                    if ($this->checkAllowFilter($attributeRef, $filterable)) {
                        $stack[] = [
                            $this->mapOperator($token) => [$attributeRef, $firstBound, $secondBound],
                        ];
                    }
                    break;

                case IbmOperator::NOT->value:
                    $attributeRef = array_pop($stack);
                    if ($attributeRef) {
                        $stack[] = [$this->mapOperator($token) => $attributeRef];
                    }
                    break;

                case IbmOperator::HAS->value:
                    $attributeRef = array_pop($stack);
                    $value = array_pop($stack);
                    $value = is_null($value) ? 1 : $this->coerceValue($value);
                    if ($this->checkAllowFilter($attributeRef, $filterable)) {
                        $stack[] = [$this->mapOperator($token) => [$attributeRef, $value]];
                    }
                    break;

                case IbmOperator::RELATION->value:
                    [$value, $relation] = array_splice($stack, 0);
                    if ($this->checkAllowFilter($relation, $filterable)) {
                        $stack[] = [$this->mapOperator($token) => [$relation, $value]];
                    }
                    break;

                case IbmOperator::AND->value:
                case IbmOperator::OR->value:
                    $stack[] = [$this->mapOperator($token) => array_splice($stack, 0)];
                    break;

                default:
                    $attributeRef = $this->coerceValue(array_pop($stack), $token);
                    $value = $this->coerceValue(array_pop($stack), $token);
                    if ($this->checkAllowFilter($attributeRef, $filterable)) {
                        $stack[] = [
                            $this->mapOperator($token, $value === null) => [$attributeRef, $value],
                        ];
                    }
                    break;
            }
        }

        return array_pop($stack);
    }

    public function coerceValue($value, $parentOperator = null): bool|int|string|float|null
    {
        // Fast check for null
        if ($value === 'null') {
            return null;
        }

        // Check if it's a constant value (enclosed in single quotes)
        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            // Extract the value without quotes
            $value = substr($value, 1, -1);

            // Check for boolean values
            if ($value === 'true' || $value === 'false') {
                return $value === 'true';
            }

            // Check for numeric values, but not for certain operators that need to preserve string format
            $isTextOperator = in_array($parentOperator, [
                IbmOperator::CONTAINS->value,
                IbmOperator::CONTAINS_RELATION->value,
                IbmOperator::STARTS_WITH->value,
                IbmOperator::STARTS_WITH_RELATION->value,
                IbmOperator::ENDS_WITH->value,
                IbmOperator::ENDS_WITH_RELATION->value,
            ]);

            if (!$isTextOperator && is_string($value) && trim($value) !== '' && is_numeric($value)) {
                return str_contains($value, '.') ? (float) $value : (int) $value;
            }

            // Check for date strings
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                return $value;
            }

            // Apply wildcard formatting for string values
            return $this->wildCardString($value, $parentOperator);
        }

        // It's an attribute reference
        return "#$value";
    }

    public function tokenizeExpression(string $expression): array
    {
        $tokens = preg_split('/[(),]/', $expression);

        return array_filter(array_map('trim', $tokens));
    }

    public function mapOperator($operator, $valueIsNull = false): string
    {
        // Special case for EQUALS with null value
        if ($operator === IbmOperator::EQUALS->value && $valueIsNull) {
            return SqlOperator::IS_NULL->value;
        }

        $this->operatorMappings ??= [
            IbmOperator::ANY_RELATION->value => SqlOperator::ANY_RELATION->value,
            IbmOperator::EQUALS->value => SqlOperator::EQUALS->value,
            IbmOperator::EQUALS_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::GREATER_OR_EQUAL_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::GREATER_THAN_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::LESS_OR_EQUAL_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::LESS_THAN_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::CONTAINS_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::STARTS_WITH_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::ENDS_WITH_RELATION->value => SqlOperator::RELATION->value,
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
            IbmOperator::RELATION->value => SqlOperator::FILTER_RELATION_HAS->value,
            IbmOperator::BETWEEN->value => SqlOperator::BETWEEN->value,
            IbmOperator::BETWEEN_RELATION->value => SqlOperator::BETWEEN_RELATION->value,
        ];

        return $this->operatorMappings[$operator] ?? SqlOperator::EQUALS->value;
    }

    private function checkAllowFilter($field, $filterable): bool
    {
        if (!$filterable) {
            return true;
        }

        if (isset($field[0]) && $field[0] === '#') {
            $field = substr($field, 1);
        }

        return in_array($field, $filterable, true);
    }

    private function wildCardString($value, $operator = null): string
    {
        return match ($operator) {
            IbmOperator::CONTAINS->value, IbmOperator::CONTAINS_RELATION->value => '%' . $value . '%',
            IbmOperator::STARTS_WITH->value, IbmOperator::STARTS_WITH_RELATION->value => $value . '%',
            IbmOperator::ENDS_WITH->value, IbmOperator::ENDS_WITH_RELATION->value => '%' . $value,
            default => $value,
        };
    }
}
