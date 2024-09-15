<?php

namespace LaraJS\Query\QueryParser;

interface SortParserInterface
{
    public function parse(array $sorts): array;
}
