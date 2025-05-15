<?php

namespace LaraJS\Query\Enum;

enum SqlOperator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '<>';
    case GREATER_THAN = '>';
    case GREATER_OR_EQUAL = '>=';
    case LESS_THAN = '<';
    case LESS_OR_EQUAL = '<=';
    case ILIKE = 'ILIKE';
    case LIKE = 'LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT_IN';
    case NOT = 'NOT';
    case AND = 'AND';
    case OR = 'OR';
    case IS_NULL = 'IS_NULL';
    case IS_NOT_NULL = 'IS_NOT_NULL';
    case HAS = 'HAS';
    case FILTER_RELATION_HAS = 'FILTER_RELATION_HAS';
    case FILTER_RELATION = 'FILTER_RELATION';
    case RELATION = 'RELATION';
    case ANY_RELATION = 'ANY_RELATION';
    case BETWEEN = 'BETWEEN';
    case BETWEEN_RELATION = 'BETWEEN_RELATION';
}
