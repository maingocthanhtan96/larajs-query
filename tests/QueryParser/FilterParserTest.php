<?php

namespace Tests\QueryParser;

use Exception;
use LaraJS\Query\QueryParser\FilterParser;
use PHPUnit\Framework\TestCase;

class FilterParserTest extends TestCase
{
    private FilterParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FilterParser;
    }

    /**
     * @throws Exception
     */
    public function testEqualsParser()
    {
        $queryString = [
            '=' => [
                '#name',
                'Smith',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'name',
                    '=',
                    'Smith',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testEqualsRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#name',
                '=',
                'Smith',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'name',
                    '=',
                    'Smith',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testLessThanParser()
    {
        $queryString = [
            '<' => [
                '#age',
                25,
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'age',
                    '<',
                    25,
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testLessThanRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#age',
                '<',
                25,
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'age',
                    '<',
                    25,
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testLessOrEqualParser()
    {
        $queryString = [
            '<=' => [
                '#lastModified',
                '2001-01-01',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'lastModified',
                    '<=',
                    '2001-01-01',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testLessOrEqualRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#lastModified',
                '<=',
                '2001-01-01',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'lastModified',
                    '<=',
                    '2001-01-01',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testGreaterThanParser()
    {
        $queryString = [
            '>' => [
                '#duration',
                '6:12:14',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'duration',
                    '>',
                    '6:12:14',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testGreaterThanRelationRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#duration',
                '>',
                '6:12:14',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'duration',
                    '>',
                    '6:12:14',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testGreaterOrEqualParser()
    {
        $queryString = [
            '>=' => [
                '#percentage',
                33.33,
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'percentage',
                    '>=',
                    33.33,
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testGreaterOrEqualRelationRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#percentage',
                '>=',
                33.33,
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'percentage',
                    '>=',
                    33.33,
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testContainsParser()
    {
        $queryString = [
            'LIKE' => [
                '#description',
                '%cooking%',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'description',
                    'LIKE',
                    '%cooking%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testContainsRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#description',
                'LIKE',
                '%cooking%',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'description',
                    'LIKE',
                    '%cooking%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testStartsWithParser()
    {
        $queryString = [
            'LIKE' => [
                '#description',
                'The%',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'description',
                    'LIKE',
                    'The%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testStartsWithRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#description',
                'LIKE',
                'The%',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'description',
                    'LIKE',
                    'The%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testEndsWithParser()
    {
        $queryString = [
            'LIKE' => [
                '#description',
                '%End',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'description',
                    'LIKE',
                    '%End',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testEndsWithRelationParser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#description',
                'LIKE',
                '%End',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'description',
                    'LIKE',
                    '%End',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testAnyParser()
    {
        $queryString = [
            'IN' => [
                '#chapter',
                'Intro',
                'Summary',
                'Conclusion',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereIn',
                'isNested' => false,
                'parameters' => [
                    'chapter',
                    [
                        'Intro',
                        'Summary',
                        'Conclusion',
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testNotParser()
    {
        $queryString = [
            'NOT' => ['IS_NULL' => '#lastName'],
        ];
        $expect = [
            [
                'fx' => 'whereNot',
                'isNested' => true,
                'parameters' => [
                    [
                        'fx' => 'whereNull',
                        'isNested' => false,
                        'parameters' => ['lastName'],
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testHasParser()
    {
        $queryString = [
            'HAS' => [
                'articles',
                2,
            ],
        ];
        $expect = [
            [
                'fx' => 'has',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    '>=',
                    2,
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testConditionLogicalOR()
    {
        $queryString = [
            'OR' => [
                [
                    'HAS' => [
                        0 => 'invoices',
                        1 => 1,
                    ],
                ],
                [
                    'HAS' => [
                        0 => 'orders',
                        1 => 1,
                    ],
                ],
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => true,
                'parameters' => [
                    0 => [
                        'fx' => 'has',
                        'isNested' => false,
                        'parameters' => [
                            'invoices',
                            '>=',
                            1,
                        ],
                    ],
                    [
                        'fx' => 'orHas',
                        'isNested' => false,
                        'parameters' => [
                            'orders',
                            '>=',
                            1,
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function testConditionLogicalAND()
    {
        $queryString = [
            'AND' => [
                [
                    'HAS' => [
                        0 => 'invoices',
                        1 => 1,
                    ],
                ],
                [
                    'HAS' => [
                        0 => 'orders',
                        1 => 1,
                    ],
                ],
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => true,
                'parameters' => [
                    0 => [
                        'fx' => 'has',
                        'isNested' => false,
                        'parameters' => [
                            'invoices',
                            '>=',
                            1,
                        ],
                    ],
                    [
                        'fx' => 'has',
                        'isNested' => false,
                        'parameters' => [
                            'orders',
                            '>=',
                            1,
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
