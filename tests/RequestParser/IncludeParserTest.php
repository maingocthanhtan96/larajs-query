<?php

namespace Tests\RequestParser;

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
        $expect = [
            'with' => ['roles', 'roles|count', 'roles|exists', 'roles.total|sum', 'roles.total|min', 'roles.total|max', 'roles.total|avg'],
            'withWhereHas' => [],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_empty_query_string()
    {
        $queryString = [];
        $filterable = ['roles', 'users'];
        $expect = [
            'with' => [],
            'withWhereHas' => [],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function test_parser_null_query_string_and_filterable()
    {
        $queryString = null;
        $filterable = null;
        $expect = [
            'with' => [],
            'withWhereHas' => [],
        ];

        $this->assertSame($expect, $this->parser->parse($queryString ?? [], $filterable));
    }

    public function test_parser_invalid_relation_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $queryString = ['invalid'];
        $filterable = ['users', 'roles'];

        $this->parser->parse($queryString, $filterable);
    }

    public function test_parser_filterable()
    {
        $queryString = ['roles', 'roles|count', 'roles.permissions|count'];
        $expect['with'] = ['roles', 'roles|count', 'roles.permissions|count'];
        $expect['withWhereHas'] = [];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles', 'roles.permissions']));
    }

    public function test_parser_except_permission_count_invalid_filterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['permissions|count', 'roles', 'roles|count', 'roles.permissions|count'];

        $this->parser->parse($queryString, ['roles', 'roles', 'roles.permissions']);
    }

    public function test_parser_one_filterable()
    {
        $queryString = ['roles:id,name|count', 'roles.permissions|count'];
        $expect['with'] = ['roles:id,name|count', 'roles.permissions|count'];
        $expect['withWhereHas'] = [];

        $this->assertSame($expect, $this->parser->parse($queryString, ['roles', 'roles.permissions']));
    }

    public function test_parser_invalid_filterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = ['roles:id,name,created_at', 'users'];

        $this->parser->parse($queryString, ['roles', 'permissions']);
    }

    public function test_parser_with_no_column_specified()
    {
        $queryString = ['users', 'roles'];
        $filterable = ['users', 'roles'];
        $expect['with'] = ['users', 'roles'];
        $expect['withWhereHas'] = [];

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
        $expect['with'] = ['users:id,name', 'roles', 'permissions:id'];
        $expect['withWhereHas'] = [];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function test_parser_with_aggregates()
    {
        $queryString = ['users|count', 'roles|exists'];
        $filterable = ['users', 'roles'];
        $expect['with'] = ['users|count', 'roles|exists'];
        $expect['withWhereHas'] = [];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function test_parser_with_aggregates_filterable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $queryString = ['users|count', 'roles|exists'];
        $filterable = ['users'];

        $this->parser->parse($queryString, $filterable);
    }

    public function test_parser_filter_relation()
    {
        $queryString = ["users|and(equals(name,'Smith'),greaterThan(age,'25'))"];
        $filterable = ['users'];
        $expect['with'] = [];
        $expect['withWhereHas'] = [
            [
                'INCLUDE_RELATION_HAS' => [
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
        ];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }

    public function test_parser_multiple_filter_relations()
    {
        $queryString = ["users|and(equals(name,'Smith'),greaterThan(age,'25'))", "categories|equals(user_id,'1')"];
        $filterable = ['users', 'categories'];
        $expect['with'] = [];
        $expect['withWhereHas'] = [
            [
                'INCLUDE_RELATION_HAS' => [
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
                'INCLUDE_RELATION_HAS' => [
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

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }
}
