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

    public function test_parser()
    {
        $queryString = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];
        $expect = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_filterable()
    {
        $queryString = ['roles', 'roles|count', 'roles.permissions|count'];
        $expect = ['roles', 'roles|count', 'roles.permissions|count'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']));
    }

    public function test_parser_except_permission_count_invalid_filterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['permissions|count', 'roles', 'roles|count', 'roles.permissions|count'];

        $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']);
    }

    public function test_parser_one_filterable()
    {
        $queryString = ['roles', 'roles|count', 'roles.permissions|count'];
        $expect = ['roles', 'roles|count', 'roles.permissions|count'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']));
    }

    public function test_parser_invalid_filterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['roles:id,name,created_at'];

        $this->parser->parse($queryString, ['roles:id,name', 'permissions']);
    }

    public function test_parser_with_no_column_specified()
    {
        $queryString = ['users', 'roles'];
        $filterable = ['users', 'roles'];
        $expect = ['users', 'roles'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function test_parser_with_invalid_relation()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['invalid:id,name'];
        $filterable = ['users:id,name'];

        $this->parser->parse($queryString, $filterable);
    }

    public function test_parser_with_mixed_relations()
    {
        $queryString = ['users:id,name', 'roles', 'permissions:id'];
        $filterable = ['users:id,name', 'roles', 'permissions:id'];
        $expect = ['users:id,name', 'roles', 'permissions:id'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function test_parser_with_aggregates()
    {
        $queryString = ['users|count', 'roles|exists'];
        $filterable = ['users|count', 'roles|exists'];
        $expect = ['users|count', 'roles|exists'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }
}
