<?php

namespace Tests\QueryParser;

use LaraJS\QueryParser\QueryParser\DateParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    private DateParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DateParser();
    }

    public function testParser()
    {
        $queryString = [
            'column' => 'updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [
            [
                'fx' => 'whereBetween',
                'isNested' => false,
                'parameters' => ['updated_at', $queryString['value']],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
