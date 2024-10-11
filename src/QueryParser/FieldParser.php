<?php

namespace LaraJS\Query\QueryParser;

class FieldParser
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
