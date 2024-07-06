<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\QueryParser\RequestParser\SearchParser;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

class SearchParserTest extends TestCase
{
    private SearchParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SearchParser();
        $model = new ModelTest();
        $this->query = Mockery::mock(Builder::class);
        $this->query->shouldReceive('getModel')->andReturn($model);
    }

    public function testParser()
    {
        $queryString = [
            'column' => 'id,name,roles.name,',
            'value' => 'Lorem',
        ];
        $expect = [
            'column' => ['id', 'name', 'roles.name'],
            'value' => 'Lorem',
        ];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }

    public function testParserFilterable()
    {
        $queryString = [
            'column' => 'id,name,roles.name,age,permissions.name',
            'value' => 'Lorem',
        ];
        $expect = [
            'column' => ['id', 'name', 'roles.name'],
            'value' => 'Lorem',
        ];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }
}
