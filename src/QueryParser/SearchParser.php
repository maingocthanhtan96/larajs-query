<?php

namespace LaraJS\Query\QueryParser;

use LaraJS\Query\Enum\Method;

class SearchParser
{
    public function parse(array $queryString): array
    {
        $column = $queryString['column'];
        $value = $queryString['value'];

        if (!$column || !$value) {
            return [];
        }

        return [
            [
                'fx' => Method::SPECIAL_LIKE->value,
                'isNested' => false,
                'parameters' => [$column, $value],
            ],
        ];
    }
}
