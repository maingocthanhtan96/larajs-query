<?php

namespace LaraJS\QueryParser\Enum;

enum IbmValueType
{
    case BOOLEAN;
    case STRING;
    case NUMBER;
    case DATE;
    case ATTRIBUTE_REF;
    case NULL;
}
