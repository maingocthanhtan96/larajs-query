<?php

namespace LaraJS\QueryParser\QueryParser;

interface SearchParserInterface
{
    public function parse(array $queryString): array;
}
