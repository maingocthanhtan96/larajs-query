<?php

namespace Tests\QueryParser;

use LaraJS\Query\Enum\Method;
use LaraJS\Query\QueryParser\SortParser;
use PHPUnit\Framework\TestCase;

class SortParserTest extends TestCase
{
    private SortParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SortParser;
    }

    public function test_parse_with_empty_array()
    {
        $sorts = [];
        $result = $this->parser->parse($sorts);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_parse_with_single_sort()
    {
        $sorts = [['name', 'asc']];
        $expected = [
            [
                'fx' => Method::ORDER_RELATION->value,
                'isNested' => false,
                'parameters' => ['name', 'asc'],
            ],
        ];

        $result = $this->parser->parse($sorts);

        $this->assertEquals($expected, $result);
    }

    public function test_parse_with_multiple_sorts()
    {
        $sorts = [
            ['name', 'asc'],
            ['created_at', 'desc'],
            ['id', 'asc'],
        ];
        $expected = [
            [
                'fx' => Method::ORDER_RELATION->value,
                'isNested' => false,
                'parameters' => ['name', 'asc'],
            ],
            [
                'fx' => Method::ORDER_RELATION->value,
                'isNested' => false,
                'parameters' => ['created_at', 'desc'],
            ],
            [
                'fx' => Method::ORDER_RELATION->value,
                'isNested' => false,
                'parameters' => ['id', 'asc'],
            ],
        ];

        $result = $this->parser->parse($sorts);

        $this->assertEquals($expected, $result);
    }
}
