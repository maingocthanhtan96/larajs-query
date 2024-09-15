<?php

namespace LaraJS\Query\QueryParser;

interface SearchParserInterface
{
    public function parse(array $queryString): array;
}
