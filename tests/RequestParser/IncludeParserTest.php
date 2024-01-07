<?php

namespace Tests\RequestParser;

use Exception;
use LaraJS\QueryParser\RequestParser\IncludeParser;
use PHPUnit\Framework\TestCase;

class IncludeParserTest extends TestCase
{
    private IncludeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IncludeParser();
    }

    public function testParser()
    {
        $queryString = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];
        $expect = [
            [
                'fx' => 'with',
                'parameters' => ['roles'],
            ],
            [
                'fx' => 'withAggregate',
                'parameters' => ['roles', '*', 'count'],
            ],
            [
                'fx' => 'withAggregate',
                'parameters' => ['roles', '*', 'exists'],
            ],
            [
                'fx' => 'withAggregate',
                'parameters' => ['roles', 'total', 'sum'],
            ],
            [
                'fx' => 'withAggregate',
                'parameters' => ['roles', 'total', 'min'],
            ],
            [
                'fx' => 'withAggregate',
                'parameters' => ['roles', 'total', 'max'],
            ],
            [
                'fx' => 'withAggregate',
                'parameters' => ['roles', 'total', 'avg'],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
