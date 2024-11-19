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

    public function testEqualsParser()
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

    public function testEqualsRelationParser()
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

    public function testLessThanParser()
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

    public function testLessThanRelationParser()
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

    public function testLessOrEqualParser()
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

    public function testLessOrEqualRelationParser()
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

    public function testGreaterThanParser()
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

    public function testGreaterThanRelationRelationParser()
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

    public function testGreaterOrEqualParser()
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

    public function testGreaterOrEqualRelationRelationParser()
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

    public function testContainsParser()
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

    public function testContainsRelationParser()
    {
        $queryString = "containsRelation(articles,description,'cooking')";
        $expect = [
            'RELATION' => [
                '#articles',
                '#description',
                'LIKE',
                '%cooking%',
            ],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testStartsWithParser()
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

    public function testStartsWithRelationParser()
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

    public function testEndsWithParser()
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

    public function testEndsWithRelationParser()
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

    public function testAnyParser()
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

    public function testNotParser()
    {

        $queryString = 'not(equals(lastName,null))';
        $expect = [
            'NOT' => ['IS_NULL' => '#lastName'],
        ];
        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testHasParser()
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

    public function testConditionLogicalOR()
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

    public function testConditionLogicalAND()
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

    public function testConditionLogicalANDFilterable()
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

    public function testFieldsFilterable()
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

    public function testFieldsWithRelationFilterable()
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
