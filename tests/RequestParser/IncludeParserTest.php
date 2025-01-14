<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\IncludeParser;
use PHPUnit\Framework\TestCase;

class IncludeParserTest extends TestCase
{
    private IncludeParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IncludeParser;
    }

    public function testParser()
    {
        $queryString = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];
        $expect = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserFilterable()
    {
        $queryString = ['roles', 'roles|count', 'roles.permissions|count'];
        $expect = ['roles', 'roles|count', 'roles.permissions|count'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']));
    }

    public function testParserExceptPermissionCountInvalidFilterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['permissions|count', 'roles', 'roles|count', 'roles.permissions|count'];

        $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']);
    }

    public function testParserOneFilterable()
    {
        $queryString = ['roles', 'roles|count', 'roles.permissions|count'];
        $expect = ['roles', 'roles|count', 'roles.permissions|count'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']));
    }

    public function testParserInvalidFilterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['roles:id,name,created_at'];

        $this->parser->parse($queryString, ['roles:id,name', 'permissions']);
    }

    public function testParserWithNoColumnSpecified()
    {
        $queryString = ['users', 'roles'];
        $filterable = ['users', 'roles'];
        $expect = ['users', 'roles'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function testParserWithInvalidRelation()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['invalid:id,name'];
        $filterable = ['users:id,name'];

        $this->parser->parse($queryString, $filterable);
    }

    public function testParserWithMixedRelations()
    {
        $queryString = ['users:id,name', 'roles', 'permissions:id'];
        $filterable = ['users:id,name', 'roles', 'permissions:id'];
        $expect = ['users:id,name', 'roles', 'permissions:id'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function testParserWithAggregates()
    {
        $queryString = ['users|count', 'roles|exists'];
        $filterable = ['users|count', 'roles|exists'];
        $expect = ['users|count', 'roles|exists'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }
}
