<?php

namespace QueryParser;

use LaraJS\Query\Enum\Method;
use LaraJS\Query\QueryParser\SearchParser;
use PHPUnit\Framework\TestCase;

class SearchParserTest extends TestCase
{
    private SearchParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SearchParser;
    }

    public function test_parser()
    {
        $queryString = [
            'column' => ['name', 'roles.name'],
            'value' => 'Lorem',
        ];
        $expect = [
            [
                'fx' => Method::SPECIAL_LIKE->value,
                'isNested' => false,
                'parameters' => [
                    $queryString['column'],
                    $queryString['value'],
                ],
            ],
        ];
        $this->assertEquals($expect, $this->parser->parse($queryString));
    }
}
