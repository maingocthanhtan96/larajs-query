<?php

namespace LaraJS\QueryParser\QueryParser;

interface SortParserInterface
{
    public function parse(array $sorts): array;
}
