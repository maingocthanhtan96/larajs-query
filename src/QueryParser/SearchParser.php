<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class SearchParser
{
    public function parse(array $queryString): array
    {
        $column = $queryString['column'] ?? null;
        $value = $queryString['value'] ?? null;

        return ($column && $value) ? [[
            'fx' => Method::SPECIAL_LIKE->value,
            'isNested' => false,
            'parameters' => [$column, $value],
        ]] : [];
    }
}
