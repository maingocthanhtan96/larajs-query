<?php

namespace Tests\RequestParser;

use Exception;
use LaraJS\QueryParser\RequestParser\SortParser;
use PHPUnit\Framework\TestCase;

class SortParserTest extends TestCase
{
    private SortParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SortParser();
    }

    public function testParser()
    {
        $queryString = '-updated_at,id,';
        $expect = [
            [
                'updated_at',
                'desc',
            ],
            [
                'id',
                'asc',
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
