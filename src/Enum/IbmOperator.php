<?php

namespace LaraJS\QueryParser\Enum;

enum IbmOperator: string
{
    case EQUALS = 'equals';
    case GREATER_THAN = 'greaterThan';
    case GREATER_OR_EQUAL = 'greaterOrEqual';
    case LESS_THAN = 'lessThan';
    case LESS_OR_EQUAL = 'lessOrEqual';
    case CONTAINS = 'contains';
    case STARTS_WITH = 'startsWith';
    case ENDS_WITH = 'endsWith';
    case ANY = 'any';
    case NOT = 'not';
    case AND = 'and';
    case OR = 'or';
    case HAS = 'has';
}
