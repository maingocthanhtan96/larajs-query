<?php

namespace LaraJS\QueryParser\QueryParser;

interface FieldParserInterface
{
    public function parse(array $fields): array;
}
