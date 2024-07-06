<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\QueryParser\RequestParser\FilterParser;
use PHPUnit\Framework\TestCase;
use Tests\ModelTest;

class FilterParserTest extends TestCase
{
    private FilterParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new FilterParser();
        $model = new ModelTest();
        $this->query = \Mockery::mock(Builder::class);
        $this->query->shouldReceive('getModel')->andReturn($model);
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }

    public function testNotParser()
    {

        $queryString = 'not(equals(lastName,null))';
        $expect = [
            'NOT' => ['IS_NULL' => '#lastName'],
        ];
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }

    public function testConditionLogicalANDFilterable()
    {
        $queryString = "and(has(orders,'1'),has(bills,'1'))";
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
        $this->assertSame($expect, $this->parser->parse($this->query, $queryString));
    }
}
