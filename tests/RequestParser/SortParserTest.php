<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\QueryParser\RequestParser\SortParser;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

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
        $model = new ModelTest();
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);
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

        $this->assertSame($expect, $this->parser->parse($query, $queryString));
    }

    public function testParserFilterable()
    {
        $model = new ModelTest();
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);
        $queryString = 'id,-created_at,';
        $expect = [
            [
                'id',
                'asc',
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($query, $queryString));
    }
}
