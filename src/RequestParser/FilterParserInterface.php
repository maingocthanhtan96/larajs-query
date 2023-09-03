<?php

namespace LaraJS\QueryParser\RequestParser;

interface FilterParserInterface
{
    public function parse(array $queryString): array;
}
