<?php

namespace LaraJS\Query\QueryParser;

interface DateParserInterface
{
    public function parse(array $queryString): array;
}
