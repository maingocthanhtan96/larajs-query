<?php

namespace LaraJS\QueryParser\Enum;

enum Operator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '<>';
    case GREATER_THAN = '>';
    case GREATER_OR_EQUAL = '>=';
    case LESS_THAN = '<';
    case LESS_OR_EQUAL = '<=';
    case LIKE = 'LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case NOT = 'NOT';
    case AND = 'AND';
    case OR = 'OR';
    case IS_NULL = 'IS NULL';
    case IS_NOT_NULL = 'IS NOT NULL';
}
