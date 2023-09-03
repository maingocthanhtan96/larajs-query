<?php

namespace LaraJS\QueryParser\QueryParser;

interface IncludeParserInterface
{
    public function parse(array $aggregates): array;
}
