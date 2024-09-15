<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\IncludeParser;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

class IncludeParserTest extends TestCase
{
    private IncludeParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IncludeParser;
        $model = new ModelTest;
        $this->query = \Mockery::mock(Builder::class);
        $this->query->shouldReceive('getModel')->andReturn($model);
    }

    public function testParser()
    {
        $model = new ModelTest;
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);

        $queryString = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];
        $expect = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }

    public function testParserFilterable()
    {
        $queryString = ['permissions|count', 'roles', 'roles|count'];
        $expect = ['roles', 'roles|count'];

        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }
}
