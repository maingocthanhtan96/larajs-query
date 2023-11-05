<?php

namespace LaraJS\QueryParser\Enum;

enum Method: string
{
    case DEFAULT = 'where';
    case NOT = 'whereNot';
    case NOT_IN = 'whereNotIn';
    case IN = 'whereIn';
    case IS_NULL = 'whereNull';
    case IS_NOT_NULL = 'whereNotNull';
    case HAS = 'has';
    case SPECIAL_LIKE = 'whereLike';
    case RELATION = 'whereRelation';

    public static function fromName(string $name): Method
    {
        return constant("self::$name");
    }
}
