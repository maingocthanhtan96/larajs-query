<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\SearchParser;
use PHPUnit\Framework\TestCase;

class SearchParserTest extends TestCase
{
    private SearchParser $parser;

    private array $defaultData = [
        'column' => '',
        'value' => '',
    ];

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SearchParser;
    }

    public function test_parser()
    {
        $queryString = [
            'column' => 'id,name,roles.name,',
            'value' => 'Lorem',
        ];
        $expect = [
            'column' => ['id', 'name', 'roles.name'],
            'value' => 'Lorem',
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $this->defaultData, null));
    }

    public function test_parser_filterable()
    {
        $queryString = [
            'column' => 'id,name,roles.name,age,permissions.name',
            'value' => 'Lorem',
        ];
        $expect = [
            'column' => ['id', 'name', 'roles.name'],
            'value' => 'Lorem',
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $this->defaultData, ['id', 'name', 'roles']));
    }
}
