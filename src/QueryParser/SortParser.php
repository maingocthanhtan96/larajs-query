<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class SortParser
{
    public function parse(array $sorts): array
    {
        $parsedArray = [];
        foreach ($sorts as $sort) {
            $parsedArray[] = [
                'fx' => Method::ORDER_RELATION->value,
                'isNested' => false,
                'parameters' => $sort,
            ];
        }

        return $parsedArray;
    }
}
