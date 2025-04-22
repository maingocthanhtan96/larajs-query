<?php

namespace RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\SearchParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    private SearchParser $parser;

    private array $defaultData = [
        'column' => '',
        'value' => [],
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
            'column' => 'updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [
            'column' => ['updated_at'],
            'value' => ['2024-01-01', '2024-12-01'],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $this->defaultData, null));
    }

    public function test_parser_filterable()
    {
        $queryString = [
            'column' => 'created_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];

        $expect = [
            'column' => '',
            'value' => [],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $this->defaultData, ['updated_at']));
    }

    public function test_multiple_parser()
    {
        $queryString = [
            'column' => 'created_at,updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];

        $expect = [
            'column' => ['created_at', 'updated_at'],
            'value' => ['2024-01-01', '2024-12-01'],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $this->defaultData, null));
    }

    public function test_multiple_parser_filterable()
    {
        $queryString = [
            'column' => 'created_at,updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [
            'column' => ['updated_at'],
            'value' => ['2024-01-01', '2024-12-01'],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $this->defaultData, ['updated_at']));
    }
}
