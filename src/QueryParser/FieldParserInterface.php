<?php

namespace LaraJS\QueryParser\QueryParser;

interface FieldParserInterface
{
    public function parse(string $queryString): array;
}
