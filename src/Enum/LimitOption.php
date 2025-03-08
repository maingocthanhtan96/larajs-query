<?php

namespace LaraJS\Query\Enum;

enum LimitOption: int
{
    case DEFAULT_LIMIT = 25;
    case MAX_LIMIT = 500;

}
