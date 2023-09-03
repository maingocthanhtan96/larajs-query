<?php

namespace LaraJS\QueryParser\QueryParser;

interface FilterParserInterface
{
    public function parse(array $filters, bool $isOr): array;
}
