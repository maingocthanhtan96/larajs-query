<?php

namespace LaraJS\QueryParser\QueryParser;

use Illuminate\Support\Str;

class FieldParser implements FieldParserInterface
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
