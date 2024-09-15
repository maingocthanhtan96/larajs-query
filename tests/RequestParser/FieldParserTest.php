<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\FieldParser;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

class FieldParserTest extends TestCase
{
    private FieldParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FieldParser;
        $model = new ModelTest;
        $this->query = Mockery::mock(Builder::class);
        $this->query->shouldReceive('getModel')->andReturn($model);
    }

    public function testParser()
    {
        $queryString = 'id,name,email,';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }

    public function testParserFilterable()
    {
        $queryString = 'id,name,age,';
        $expect = ['id', 'name'];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }
}
