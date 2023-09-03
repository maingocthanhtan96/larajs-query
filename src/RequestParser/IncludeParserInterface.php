<?php

namespace LaraJS\QueryParser\RequestParser;

interface IncludeParserInterface
{
    public function parse(array $queryString): array;
}
