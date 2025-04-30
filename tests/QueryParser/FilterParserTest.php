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
    public function test_equals_parser()
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
    public function test_equals_relation_parser()
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
    public function test_less_than_parser()
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
    public function test_less_than_relation_parser()
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
    public function test_less_or_equal_parser()
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
    public function test_less_or_equal_relation_parser()
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
    public function test_greater_than_parser()
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
    public function test_greater_than_relation_relation_parser()
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
    public function test_greater_or_equal_parser()
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
    public function test_greater_or_equal_relation_relation_parser()
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
    public function test_contains_parser()
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

    public function test_contains_number_parser()
    {
        $queryString = [
            'LIKE' => [
                '#card',
                '%1234567890%',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'card',
                    'LIKE',
                    '%1234567890%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function test_contains_relation_parser()
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

    public function test_contains_relation_number_parser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#card',
                'LIKE',
                '%1234567890%',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'card',
                    'LIKE',
                    '%1234567890%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function test_starts_with_parser()
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

    public function test_starts_with_number_parser()
    {
        $queryString = [
            'LIKE' => [
                '#card',
                '1234567890%',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'card',
                    'LIKE',
                    '1234567890%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function test_starts_with_relation_parser()
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

    public function test_starts_with_relation_number_parser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#number',
                'LIKE',
                '1234567890%',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'number',
                    'LIKE',
                    '1234567890%',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function test_ends_with_parser()
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

    public function test_ends_with_number_parser()
    {
        $queryString = [
            'LIKE' => [
                '#card',
                '%1234567890',
            ],
        ];
        $expect = [
            [
                'fx' => 'where',
                'isNested' => false,
                'parameters' => [
                    'card',
                    'LIKE',
                    '%1234567890',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function test_ends_with_relation_parser()
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

    public function test_ends_with_relation_number_parser()
    {
        $queryString = [
            'RELATION' => [
                '#articles',
                '#card',
                'LIKE',
                '%1234567890',
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelation',
                'isNested' => false,
                'parameters' => [
                    'articles',
                    'card',
                    'LIKE',
                    '%1234567890',
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    /**
     * @throws Exception
     */
    public function test_any_parser()
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

    public function test_any_relation_parser()
    {
        $queryString = [
            'ANY_RELATION' => [
                '#chapter',
                '#name',
                'IN',
                [
                    'Intro',
                    'Summary',
                    'Conclusion',
                ],
            ],
        ];
        $expect = [
            [
                'fx' => 'whereRelationIn',
                'isNested' => false,
                'parameters' => [
                    'chapter',
                    'name',
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
    public function test_not_parser()
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
    public function test_has_parser()
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
    public function test_condition_logical_or()
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
    public function test_condition_logical_and()
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

    public function test_where_has_parser()
    {
        $queryString = [
            'RELATION_HAS' => [
                'comments',
                [
                    'LIKE' => [
                        '#content',
                        '%cooking%',
                    ],
                ],
            ],
        ];
        $expect = [
            [
                'fx' => 'whereHas',
                'isNested' => true,
                'parameters' => [
                    'comments',
                    [
                        'fx' => 'where',
                        'isNested' => false,
                        'parameters' => [
                            'content',
                            'LIKE',
                            '%cooking%',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_where_has_nested_parser()
    {
        $queryString = [
            'RELATION_HAS' => [
                'comments',
                [
                    'AND' => [
                        [
                            'RELATION' => [
                                '#articles',
                                '#description',
                                'LIKE',
                                '%cooking%',
                            ],
                        ],
                        [
                            '<=' => [
                                '#lastModified',
                                '2001-01-01',
                            ],
                        ],
                        [
                            '>' => [
                                '#age',
                                25,
                            ],
                        ],

                        [
                            '=' => [
                                '#name',
                                'Smith',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expect = [
            [
                'fx' => 'whereHas',
                'isNested' => true,
                'parameters' => [
                    'comments',
                    [
                        'fx' => 'where',
                        'isNested' => true,
                        'parameters' => [
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
                            [
                                'fx' => 'where',
                                'isNested' => false,
                                'parameters' => [
                                    'lastModified',
                                    '<=',
                                    '2001-01-01',
                                ],
                            ],
                            [
                                'fx' => 'where',
                                'isNested' => false,
                                'parameters' => [
                                    'age',
                                    '>',
                                    25,
                                ],
                            ],
                            [
                                'fx' => 'where',
                                'isNested' => false,
                                'parameters' => [
                                    'name',
                                    '=',
                                    'Smith',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_complex_nested_relation()
    {
        $queryString = [
            'RELATION_HAS' => [
                'posts',
                [
                    'AND' => [
                        [
                            'RELATION_HAS' => [
                                'comments',
                                [
                                    'IS_NULL' => '#deletedAt',
                                ],
                            ],
                        ],
                        [
                            'NOT' => [
                                'IS_NULL' => '#publishedAt',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'whereHas',
                'isNested' => true,
                'parameters' => [
                    'posts',
                    [
                        'fx' => 'where',
                        'isNested' => true,
                        'parameters' => [
                            [
                                'fx' => 'whereHas',
                                'isNested' => true,
                                'parameters' => [
                                    'comments',
                                    [
                                        'fx' => 'whereNull',
                                        'isNested' => false,
                                        'parameters' => ['deletedAt'],
                                    ],
                                ],
                            ],
                            [
                                'fx' => 'whereNot',
                                'isNested' => true,
                                'parameters' => [
                                    [
                                        'fx' => 'whereNull',
                                        'isNested' => false,
                                        'parameters' => ['publishedAt'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_relation_with_multiple_conditions()
    {
        $queryString = [
            'RELATION_HAS' => [
                'orders',
                [
                    'OR' => [
                        [
                            '>' => ['#total', 1000],
                        ],
                        [
                            'IS_NULL' => '#canceledAt',
                        ],
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'whereHas',
                'isNested' => true,
                'parameters' => [
                    'orders',
                    [
                        'fx' => 'where',
                        'isNested' => true,
                        'parameters' => [
                            [
                                'fx' => 'where',
                                'isNested' => false,
                                'parameters' => ['total', '>', 1000],
                            ],
                            [
                                'fx' => 'orWhereNull',
                                'isNested' => false,
                                'parameters' => ['canceledAt'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_relation_with_null_check()
    {
        $queryString = [
            'RELATION_HAS' => [
                'user',
                [
                    'IS_NOT_NULL' => '#email',
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'whereHas',
                'isNested' => true,
                'parameters' => [
                    'user',
                    [
                        'fx' => 'whereNotNull',
                        'isNested' => false,
                        'parameters' => ['email'],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_include_filter_relation_parser()
    {
        $queryString = [
            'INCLUDE_RELATION_HAS' => [
                'posts',
                [
                    'LIKE' => [
                        '#title',
                        '%test%',
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'posts',
                    [
                        'fx' => 'where',
                        'isNested' => false,
                        'parameters' => [
                            'title',
                            'LIKE',
                            '%test%',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_include_filter_relation_with_nested_conditions()
    {
        $queryString = [
            'INCLUDE_RELATION_HAS' => [
                'comments',
                [
                    'AND' => [
                        [
                            'IS_NOT_NULL' => '#publishedAt',
                        ],
                        [
                            'LIKE' => [
                                '#content',
                                '%important%',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'comments',
                    [
                        'fx' => 'where',
                        'isNested' => true,
                        'parameters' => [
                            [
                                'fx' => 'whereNotNull',
                                'isNested' => false,
                                'parameters' => ['publishedAt'],
                            ],
                            [
                                'fx' => 'where',
                                'isNested' => false,
                                'parameters' => [
                                    'content',
                                    'LIKE',
                                    '%important%',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_include_filter_relation_with_simple_condition()
    {
        $queryString = [
            'INCLUDE_RELATION_HAS' => [
                'comments',
                [
                    '=' => [
                        '#status',
                        'approved',
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'comments',
                    [
                        'fx' => 'where',
                        'isNested' => false,
                        'parameters' => [
                            'status',
                            '=',
                            'approved',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
