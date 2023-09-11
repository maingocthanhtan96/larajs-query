<?php

namespace LaraJS\QueryParser\RequestParser;

interface FilterParserInterface
{
    public function parse(string|array $queryString): array;
}
