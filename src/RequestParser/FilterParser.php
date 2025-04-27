<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Support\Arr;
use LaraJS\Query\Enum\IbmOperator;
use LaraJS\Query\Enum\IbmValueType;
use LaraJS\Query\Enum\SqlOperator;

class FilterParser
{
    /**
     * Filter parser
     *
     * @param  string  $queryString
     * @param  ?array<string>  $filterable
     * @return array
     */
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

    public function parseExpression(string $expression, array $filterable)
    {
        $tokens = $this->tokenizeExpression($expression);
        $stack = [];

        foreach (array_reverse($tokens) as $token) {
            $isOperator = in_array($token, array_map(fn(IbmOperator $operator) => $operator->value, IbmOperator::cases()), false);
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
                    $attributeRefRelation = $this->coerceValue(array_pop($stack), $token);
                    $attributeRefField = $this->coerceValue(array_pop($stack), $token);
                    $operator = match ($token) {
                        IbmOperator::ANY_RELATION->value => SqlOperator::IN->value,
                        IbmOperator::EQUALS_RELATION->value => SqlOperator::EQUALS->value,
                        IbmOperator::GREATER_OR_EQUAL_RELATION->value => SqlOperator::GREATER_OR_EQUAL->value,
                        IbmOperator::GREATER_THAN_RELATION->value => SqlOperator::GREATER_THAN->value,
                        IbmOperator::LESS_OR_EQUAL_RELATION->value => SqlOperator::LESS_OR_EQUAL->value,
                        IbmOperator::LESS_THAN_RELATION->value => SqlOperator::LESS_THAN->value,
                        default => SqlOperator::LIKE->value,
                    };
                    $value = $token === IbmOperator::ANY_RELATION->value
                        ? array_map(fn($v) => $this->coerceValue($v), array_reverse(array_splice($stack, -count($stack), count($stack), [])))
                        : $this->coerceValue(array_pop($stack), $token);
                    if ($this->checkAllowFilter($attributeRefRelation, $filterable)) {
                        $stack[] = [
                            $this->mapOperator($token, !is_array($value) && $value === null) => [
                                $attributeRefRelation,
                                $attributeRefField,
                                $operator,
                                $value,
                            ],
                        ];
                    }

                    break;

                case IbmOperator::EQUALS->value:
                    if ($this->isNullString($stack[count($stack) - 2])) {
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

                case IbmOperator::NOT->value:
                    $attributeRef = array_pop($stack);
                    if ($attributeRef) {
                        $stack[] = [$this->mapOperator($token) => $attributeRef];
                    }
                    break;

                case IbmOperator::HAS->value:
                    $attributeRef = array_pop($stack);
                    $value = $this->coerceValue(array_pop($stack));
                    if ($this->checkAllowFilter($attributeRef, $filterable)) {
                        $stack[] = [$this->mapOperator($token) => [$attributeRef, $value]];
                    }
                    break;

                case IbmOperator::RELATION_HAS->value:
                case IbmOperator::INCLUDE_RELATION->value:
                    [$value, $relation] = array_splice($stack, 0);
                    $stack[] = [$this->mapOperator($token) => [$relation, $value]];
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
        if ($this->isNullString($value)) {
            return null;
        }

        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            // constant value
            $value = substr($value, 1, -1);
            if ($this->isBooleanString($value)) {
                return strtolower($value) === 'true';
            }
            if (
                $this->isNumberString($value) &&
                !in_array($parentOperator, [
                    IbmOperator::CONTAINS->value,
                    IbmOperator::CONTAINS_RELATION->value,
                    IbmOperator::STARTS_WITH->value,
                    IbmOperator::STARTS_WITH_RELATION->value,
                    IbmOperator::ENDS_WITH->value,
                    IbmOperator::ENDS_WITH_RELATION->value,
                ])) {
                return str_contains($value, '.') ? (float) $value : (int) $value;
            }
            if ($this->isDateString($value)) {
                return $value;
            }

            return $this->wildCardString($value, $parentOperator); // string
        }

        // attribute reference
        return "#$value";
    }

    public function tokenizeExpression(string $expression): array
    {
        $delimiters = ['(', ')', ','];
        $tokens = [$expression];

        foreach ($delimiters as $delimiter) {
            $tokens = array_reduce(
                $tokens,
                fn($carry, $token) => array_merge($carry, array_map('trim', explode($delimiter, $token))),
                []
            );
        }

        return array_values(array_filter($tokens));
    }

    public function mapOperator($operator, $valueIsNull = false): string
    {
        return match ($operator) {
            IbmOperator::ANY_RELATION->value => SqlOperator::ANY_RELATION->value,
            IbmOperator::EQUALS->value => $valueIsNull ? SqlOperator::IS_NULL->value : SqlOperator::EQUALS->value,
            IbmOperator::EQUALS_RELATION->value,
            IbmOperator::GREATER_OR_EQUAL_RELATION->value,
            IbmOperator::GREATER_THAN_RELATION->value,
            IbmOperator::LESS_OR_EQUAL_RELATION->value,
            IbmOperator::LESS_THAN_RELATION->value,
            IbmOperator::CONTAINS_RELATION->value,
            IbmOperator::STARTS_WITH_RELATION->value,
            IbmOperator::ENDS_WITH_RELATION->value => SqlOperator::RELATION->value,
            IbmOperator::GREATER_THAN->value => SqlOperator::GREATER_THAN->value,
            IbmOperator::GREATER_OR_EQUAL->value => SqlOperator::GREATER_OR_EQUAL->value,
            IbmOperator::LESS_THAN->value => SqlOperator::LESS_THAN->value,
            IbmOperator::LESS_OR_EQUAL->value => SqlOperator::LESS_OR_EQUAL->value,
            IbmOperator::CONTAINS->value,
            IbmOperator::STARTS_WITH->value,
            IbmOperator::ENDS_WITH->value => SqlOperator::LIKE->value,
            IbmOperator::ANY->value => SqlOperator::IN->value,
            IbmOperator::NOT->value => SqlOperator::NOT->value,
            IbmOperator::AND->value => SqlOperator::AND->value,
            IbmOperator::OR->value => SqlOperator::OR->value,
            IbmOperator::HAS->value => SqlOperator::HAS->value,
            IbmOperator::RELATION_HAS->value => SqlOperator::RELATION_HAS->value,
            IbmOperator::INCLUDE_RELATION->value => SqlOperator::INCLUDE_RELATION_HAS->value,
        };
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
            }

            return IbmValueType::STRING->name;
        }

        // Return null for unsupported types
        return null;
    }

    private function checkAllowFilter($field, $filterable): bool
    {
        $field = $this->removeHashFromString($field);
        if (!$filterable) {
            return true;
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

    private function isDateString($value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function isNumberString($value): bool
    {
        return is_string($value) && trim($value) !== '' && is_numeric($value);
    }

    private function isBooleanString($value): bool
    {
        return in_array($value, ['true', 'false']);
    }

    private function isNullString($value): bool
    {
        return $value === 'null';
    }

    private function removeHashFromString($str): string
    {
        return str_replace('#', '', $str);
    }
}
