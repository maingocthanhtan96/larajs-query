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
        $queryString = ['permissions|count', 'roles', 'roles|count', 'roles.permissions|count'];
        $expect = ['roles', 'roles|count', 'roles.permissions|count'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles|count', 'roles.permissions|count']));
    }

    public function testParserOneFilterable()
    {
        $queryString = ['permissions|count', 'roles', 'roles|count', 'roles.permissions|count'];
        $expect = ['roles'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles']));
    }
}
