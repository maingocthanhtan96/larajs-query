<?php

namespace LaraJS\Query\QueryParser;

class SelectParser
{
    public function parse(array $fields): array
    {
        if (!$fields) {
            return [];
        }

        return [
            [
                'fx' => 'select',
                'isNested' => false,
                'parameters' => $fields,
            ],
        ];
    }
}
