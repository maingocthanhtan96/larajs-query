<?php

namespace LaraJS\QueryParser\QueryParser;

interface DateParserInterface
{
    public function parse(array $queryString): array;
}
