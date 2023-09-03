<?php

namespace LaraJS\QueryParser\Enum;

enum FilterStyle
{
    case MONGO_DB;
    case IBM;

    public function get(): string
    {
        return match ($this) {
            self::IBM => 'IBM',
            self::MONGO_DB => 'MONGO_DB',
        };
    }
}
