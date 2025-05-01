<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class SelectParser
{
    public function parse(array $fields): array
    {
        if (!$fields) {
            return [];
        }

        return [
            [
                'fx' => Method::SELECT->value,
                'isNested' => false,
                'parameters' => $fields,
            ],
        ];
    }
}
