<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\DateParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    private DateParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DateParser;
    }

    public function testParser()
    {
        $queryString = [
            'column' => 'updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [
            'column' => 'updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserFilterable()
    {
        $queryString = [
            'column' => 'created_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [];

        $this->assertSame($expect, $this->parser->parse($queryString, ['updated_at']));
    }
}
