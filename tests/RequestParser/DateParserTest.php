<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\QueryParser\RequestParser\DateParser;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

class DateParserTest extends TestCase
{
    private DateParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DateParser();
        $model = new ModelTest();
        $this->query = Mockery::mock(Builder::class);
        $this->query->shouldReceive('getModel')->andReturn($model);
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

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }

    public function testParserFilterable()
    {
        $queryString = [
            'column' => 'created_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }
}
