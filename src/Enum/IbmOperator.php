<?php

namespace LaraJS\Query\Enum;

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
    case EQUALS_RELATION = 'equalsRelation';
    case GREATER_THAN_RELATION = 'greaterThanRelation';
    case GREATER_OR_EQUAL_RELATION = 'greaterOrEqualRelation';
    case LESS_THAN_RELATION = 'lessThanRelation';
    case LESS_OR_EQUAL_RELATION = 'lessOrEqualRelation';
    case CONTAINS_RELATION = 'containsRelation';
    case STARTS_WITH_RELATION = 'startsWithRelation';
    case ENDS_WITH_RELATION = 'endsWithRelation';
    case NOT = 'not';
    case AND = 'and';
    case OR = 'or';
    case HAS = 'has';
}
