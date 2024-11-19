<?php

namespace Tests\QueryParser;

use LaraJS\Query\QueryParser\SelectParser;
use PHPUnit\Framework\TestCase;

class SelectParserTest extends TestCase
{
    private SelectParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SelectParser;
    }

    public function testParser()
    {
        $queryString = ['id', 'name', 'email'];
        $expect = [
            [
                'fx' => 'select',
                'isNested' => false,
                'parameters' => $queryString,
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
