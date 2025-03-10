<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\FilterParser;
use PHPUnit\Framework\TestCase;

class FilterParserTest extends TestCase
{
    private FilterParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FilterParser;
    }

    public function test_equals_parser()
    {
        $queryString = "equals(name,'Smith')";
        $expect = [
            '=' => [
                '#name',
                'Smith',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_equals_relation_parser()
    {
        $queryString = "equalsRelation(articles, name,'Smith')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#name',
                '=',
                'Smith',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_less_than_parser()
    {
        $queryString = "lessThan(age,'25')";
        $expect = [
            '<' => [
                '#age',
                25,
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_less_than_relation_parser()
    {
        $queryString = "lessThanRelation(articles,age,'25')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#age',
                '<',
                25,
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_less_or_equal_parser()
    {
        $queryString = "lessOrEqual(lastModified,'2001-01-01')";
        $expect = [
            '<=' => [
                '#lastModified',
                '2001-01-01',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_less_or_equal_relation_parser()
    {
        $queryString = "lessOrEqualRelation(articles,lastModified,'2001-01-01')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#lastModified',
                '<=',
                '2001-01-01',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_greater_than_parser()
    {
        $queryString = "greaterThan(duration,'6:12:14')";
        $expect = [
            '>' => [
                '#duration',
                '6:12:14',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_greater_than_relation_relation_parser()
    {
        $queryString = "greaterThanRelation(articles,duration,'6:12:14')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#duration',
                '>',
                '6:12:14',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_greater_or_equal_parser()
    {
        $queryString = "greaterOrEqual(percentage,'33.33')";
        $expect = [
            '>=' => [
                '#percentage',
                33.33,
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_greater_or_equal_relation_relation_parser()
    {
        $queryString = "greaterOrEqualRelation(articles,percentage,'33.33')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#percentage',
                '>=',
                33.33,
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_contains_parser()
    {
        $queryString = "contains(description,'cooking')";
        $expect = [
            'LIKE' => [
                '#description',
                '%cooking%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_contains_number_parser()
    {
        $queryString = "contains(card,'1234567890')";
        $expect = [
            'LIKE' => [
                '#card',
                '%1234567890%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_contains_relation_number_parser()
    {
        $queryString = "containsRelation(articles,card,'1234567890')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#card',
                'LIKE',
                '%1234567890%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_starts_with_parser()
    {
        $queryString = "startsWith(description,'The')";
        $expect = [
            'LIKE' => [
                '#description',
                'The%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_starts_with_number_parser()
    {
        $queryString = "startsWith(card,'1234567890')";
        $expect = [
            'LIKE' => [
                '#card',
                '1234567890%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_starts_with_relation_parser()
    {
        $queryString = "startsWithRelation(articles,description,'The')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#description',
                'LIKE',
                'The%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_starts_with_relation_number_parser()
    {
        $queryString = "startsWithRelation(articles,card,'1234567890')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#card',
                'LIKE',
                '1234567890%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_ends_with_parser()
    {
        $queryString = "endsWith(description,'End')";
        $expect = [
            'LIKE' => [
                '#description',
                '%End',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_ends_with_number_parser()
    {
        $queryString = "endsWith(card,'1234567890')";
        $expect = [
            'LIKE' => [
                '#card',
                '%1234567890',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_ends_with_relation_parser()
    {
        $queryString = "endsWithRelation(articles,description,'End')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#description',
                'LIKE',
                '%End',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_ends_with_relation_number_parser()
    {
        $queryString = "endsWithRelation(articles,card,'1234567890')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#card',
                'LIKE',
                '%1234567890',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_any_parser()
    {
        $queryString = "any(chapter,'Intro','Summary','Conclusion')";
        $expect = [
            'IN' => [
                '#chapter',
                'Intro',
                'Summary',
                'Conclusion',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_not_parser()
    {

        $queryString = 'not(equals(lastName,null))';
        $expect = [
            'NOT' => ['IS_NULL' => '#lastName'],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_has_parser()
    {
        // count(articles) >= 2
        $queryString = "has(articles,'2')";
        $expect = [
            'HAS' => [
                'articles',
                2,
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_condition_logical_or()
    {
        $queryString = "or(has(orders,'1'),has(invoices,'1'))";
        $expect = [
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
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_condition_logical_and()
    {
        $queryString = "and(has(orders,'1'),has(invoices,'1'))";
        $expect = [
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
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_condition_logical_and_filterable()
    {
        $queryString = "and(has(orders,'1'),has(bills,'1'))"; // bills not in filter in model
        $expect = [
            'AND' => [
                [
                    'HAS' => [
                        0 => 'orders',
                        1 => 1,
                    ],
                ],
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, ['orders']));
    }

    public function test_fields_filterable()
    {
        $queryString = "and(equals(name,'Smith'),greaterThan(age,'25'),lessOrEqual(lastModified,'2001-01-01'),contains(description,'cooking'),contains(content,'cooking'))";
        $expect = [
            'AND' => [
                [
                    'LIKE' => [
                        '#description',
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
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, ['name', 'age', 'lastModified', 'description']));
    }

    public function test_fields_with_relation_filterable()
    {
        $queryString = "and(equals(name,'Smith'),greaterThan(age,'25'),lessOrEqual(lastModified,'2001-01-01'),containsRelation(articles,description,'cooking'))";
        $expect = [
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
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, ['name', 'age', 'lastModified', 'description', 'articles']));
    }
}
