<?php

namespace Tests\QueryParser;

use LaraJS\Query\QueryParser\FieldParser;
use PHPUnit\Framework\TestCase;

class FieldParserTest extends TestCase
{
    private FieldParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FieldParser;
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
