<?php

namespace QueryParser;

use LaraJS\Query\QueryParser\IncludeParser;
use PHPUnit\Framework\TestCase;

class IncludeParserTest extends TestCase
{
    private IncludeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IncludeParser;
    }

    public function testParser()
    {
        $queryString = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg', 'roles.permissions'];

        $expect = [
            [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => ['roles', 'roles.permissions'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', '*', 'count'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', '*', 'exists'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'sum'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'min'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'max'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'avg'],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
