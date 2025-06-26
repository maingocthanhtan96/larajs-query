<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class SelectParser
{
    public function parse(array $fields): array
    {
        return $fields ? [[
            'fx' => Method::SELECT->value,
            'isNested' => false,
            'parameters' => $fields,
        ]] : [];
    }
}
