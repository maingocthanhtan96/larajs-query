<?php

namespace LaraJS\Query\QueryParser;

interface IncludeParserInterface
{
    public function parse(array $aggregates): array;
}
