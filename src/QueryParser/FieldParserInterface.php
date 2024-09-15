<?php

namespace LaraJS\Query\QueryParser;

interface FieldParserInterface
{
    public function parse(array $fields): array;
}
