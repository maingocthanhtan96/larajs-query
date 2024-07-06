<?php

namespace LaraJS\QueryParser\QueryParser;

use LaraJS\QueryParser\Enum\Method;

class SearchParser implements SearchParserInterface
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
