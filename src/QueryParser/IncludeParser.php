<?php

namespace LaraJS\QueryParser\QueryParser;

class IncludeParser implements IncludeParserInterface
{
    public function parse(array $aggregates): array
    {
        $parsedArray = [];
        foreach ($aggregates as $aggregate) {
            $parsedArray[] = [
                'fx' => $aggregate['fx'],
                'isNested' => false,
                'parameters' => $aggregate['parameters'],
            ];
        }

        return $parsedArray;
    }
}
