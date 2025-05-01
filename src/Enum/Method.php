<?php

namespace LaraJS\Query\Enum;

enum Method: string
{
    case DEFAULT = 'where';
    case NOT = 'whereNot';
    case NOT_IN = 'whereNotIn';
    case IN = 'whereIn';
    case IS_NULL = 'whereNull';
    case IS_NOT_NULL = 'whereNotNull';
    case HAS = 'has';
    case SPECIAL_LIKE = 'whereLikeRelationship';
    case RELATION = 'whereRelation';
    case ANY_RELATION = 'whereRelationIn';
    case FILTER_RELATION_HAS = 'whereHas';
    case DATE_BETWEEN = 'whereDateBetween';
    case WITH = 'with';
    case WITH_AGGREGATE = 'withAggregate';
    case SELECT = 'select';

    public static function fromName(string $name): Method
    {
        return constant("self::$name");
    }
}
