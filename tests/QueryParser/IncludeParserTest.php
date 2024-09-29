<?php

namespace QueryParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\QueryParser\IncludeParser;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

class IncludeParserTest extends TestCase
{
    private IncludeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IncludeParser;
    }

    public function testParser()
    {
        $model = new ModelTest;
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);
        // ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg', 'roles.permissions', 'roles.permissions|count']
        $queryString = ['roles.permissions|count'];
        $expect = [
            [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => ['roles'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', '*', 'count'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', '*', 'exists'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'sum'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'min'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'max'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', 'total', 'avg'],
            ],
            [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => ['roles.permissions'],
            ],
            [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => ['roles.permissions', '*', 'count'],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
