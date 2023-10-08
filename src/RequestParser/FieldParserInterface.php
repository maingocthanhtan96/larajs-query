<?php

namespace LaraJS\QueryParser\RequestParser;

interface FieldParserInterface
{
    public function parse(string $queryString): array;
}
