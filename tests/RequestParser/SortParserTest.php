<?php

namespace Tests\RequestParser;

use LaraJS\Query\RequestParser\SortParser;
use PHPUnit\Framework\TestCase;

class SortParserTest extends TestCase
{
    private SortParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SortParser;
    }

    public function test_parser()
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

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_filterable()
    {
        $queryString = 'id,-created_at,';
        $expect = [
            [
                'id',
                'asc',
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, ['id']));
    }
}
