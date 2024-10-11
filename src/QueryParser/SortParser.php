<?php

namespace LaraJS\Query\QueryParser;

class SortParser
{
    public function parse(array $sorts): array
    {
        $parsedArray = [];
        foreach ($sorts as $sort) {
            $parsedArray[] = [
                'fx' => 'orderByRelationship',
                'isNested' => false,
                'parameters' => $sort,
            ];
        }

        return $parsedArray;
    }
}
