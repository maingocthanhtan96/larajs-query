<?php

namespace Tests\QueryParser;

use LaraJS\Query\QueryParser\IncludeParser;
use PHPUnit\Framework\TestCase;

class IncludeParserTest extends TestCase
{
    private IncludeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IncludeParser;
    }

    public function test_parser()
    {
        $queryString['with'] = ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg', 'roles.permissions'];
        $queryString['filterWith'] = [];

        $expect = [
            [
                'fx' => 'with',
                'isNested' => false,
                'parameters' => ['roles', 'roles.permissions'],
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
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_parser_with_where_has()
    {
        $queryString['with'] = [];
        $queryString['filterWith'] = [
            'FILTER_RELATION' => [
                'users',
                [
                    'AND' => [
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
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'users',
                    [
                        'fx' => 'where',
                        'isNested' => true,
                        'parameters' => [
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


    public function test_empty_input()
    {
        $queryString['with'] = [];
        $queryString['filterWith'] = [];

        $this->assertSame([], $this->parser->parse($queryString));
    }

    public function test_nested_relations_with_count()
    {
        $queryString['with'] = ['permissions|count', 'roles.users|count'];
        $queryString['filterWith'] = [];

        $expect = [
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['permissions', '*', 'count'],
            ],
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', '*', 'count'],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_mixed_with_and_where_has()
    {
        $queryString['with'] = ['roles|count'];
        $queryString['filterWith'] = [
            'FILTER_RELATION' => [
                'users',
                [
                    '=' => [
                        '#active',
                        true,
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['roles', '*', 'count'],
            ],
            [
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'users',
                    [
                        'fx' => 'where',
                        'isNested' => false,
                        'parameters' => [
                            'active',
                            '=',
                            true,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_deep_nested_relations()
    {
        $queryString['with'] = ['company.departments.employees|count'];
        $queryString['filterWith'] = [];

        $expect = [
            [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => ['company', '*', 'count'],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_parser_assign_work_users()
    {
        $queryString['with'] = [];
        $queryString['filterWith'] = [
            'FILTER_RELATION' => [
                'categories',
                [
                    '=' => [
                        '#user_id',
                        1,
                    ],
                ],
            ],
        ];

        $expect = [
            [
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'categories',
                    [
                        'fx' => 'where',
                        'isNested' => false,
                        'parameters' => [
                            'user_id',
                            '=',
                            1,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }

    public function test_parser_combined_where_has_conditions()
    {
        $queryString['with'] = [];
        $queryString['filterWith'] = [
            [
                'FILTER_RELATION' => [
                    'users',
                    [
                        'AND' => [
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
            ],
            [
                'FILTER_RELATION' => [
                    'categories',
                    [
                        '=' => [
                            '#user_id',
                            1,
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
                    'users',
                    [
                        'fx' => 'where',
                        'isNested' => true,
                        'parameters' => [
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
            [
                'fx' => 'with',
                'isNested' => true,
                'parameters' => [
                    'categories',
                    [
                        'fx' => 'where',
                        'isNested' => false,
                        'parameters' => [
                            'user_id',
                            '=',
                            1,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString));
    }
}
