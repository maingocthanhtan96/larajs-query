<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\SearchParser;
use PHPUnit\Framework\TestCase;

class SearchParserTest extends TestCase
{
    private SearchParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SearchParser;
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

        $this->assertSame($expect, $this->parser->parse($queryString, []));
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

        $this->assertSame($expect, $this->parser->parse($queryString, ['id', 'name', 'roles']));
    }
}
