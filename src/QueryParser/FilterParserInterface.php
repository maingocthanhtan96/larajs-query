<?php

namespace LaraJS\Query\QueryParser;

interface FilterParserInterface
{
    public function parse(array $filters, bool $isOr): array;
}
