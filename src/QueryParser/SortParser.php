<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class SortParser
{
    public function parse(array $sorts): array
    {
        return array_map(fn($sort) => [
            'fx' => Method::ORDER_RELATION->value,
            'isNested' => false,
            'parameters' => $sort,
        ], $sorts);
    }
}
