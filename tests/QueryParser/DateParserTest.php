<?php

namespace Tests\QueryParser;

use Carbon\Carbon;
use LaraJS\Query\QueryParser\DateParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    private DateParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DateParser;
    }

    public function test_parser()
    {
        $queryString = [
            'column' => 'updated_at',
            'value' => ['2024-01-01', '2024-12-01'],
        ];
        $expect = [
            [
                'fx' => 'whereBetween',
                'isNested' => false,
                'parameters' => [
                    'updated_at',
                    [
                        Carbon::parse($queryString['value'][0])->startOfDay(),
                        Carbon::parse($queryString['value'][1])->endOfDay(),
                    ],
                ],
            ],
        ];

        $this->assertEquals($expect, $this->parser->parse($queryString));
    }

    public function test_parser_date_time()
    {
        $queryString = [
            'column' => 'updated_at',
            'value' => ['2024-01-01 00:00:00', '2024-01-15 23:59:59'],
        ];
        $expect = [
            [
                'fx' => 'whereBetween',
                'isNested' => false,
                'parameters' => [
                    'updated_at',
                    [
                        Carbon::parse($queryString['value'][0])->startOfDay(),
                        Carbon::parse($queryString['value'][1])->endOfDay(),
                    ],
                ],
            ],
        ];

        $this->assertEquals($expect, $this->parser->parse($queryString));
    }

    public function test_parser_invalid_date_format()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = [
            'column' => 'updated_at',
            'value' => ['invalid-date', '2024-01-01'],
        ];

        $this->parser->parse($queryString);
    }
}
