<?php

namespace LaraJS\QueryParser\RequestParser;

interface SortParserInterface
{
    public function parse(string $queryString): array;
}
