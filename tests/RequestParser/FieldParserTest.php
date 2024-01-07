<?php

namespace Tests\RequestParser;

use Exception;
use LaraJS\QueryParser\RequestParser\FieldParser;
use PHPUnit\Framework\TestCase;

class FieldParserTest extends TestCase
{
    private FieldParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FieldParser();
    }

    public function testParser()
    {
        $queryString = 'id,name,email,';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
