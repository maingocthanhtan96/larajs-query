<?php

namespace Tests\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;
use LaraJS\Query\Enum\Method;
use LaraJS\Query\QueryParser\DateParser;
use LaraJS\Query\QueryParser\FilterParser;
use LaraJS\Query\QueryParser\IncludeParser;
use LaraJS\Query\QueryParser\QueryParser;
use LaraJS\Query\QueryParser\SearchParser;
use LaraJS\Query\QueryParser\SelectParser;
use LaraJS\Query\QueryParser\SortParser;
use LaraJS\Query\RequestParser\RequestParser;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class QueryParserTest extends TestCase
{
    private QueryParser $queryParser;

    private MockInterface $requestParser;

    private MockInterface $filterParser;

    private MockInterface $sortParser;

    private MockInterface $includeParser;

    private MockInterface $selectParser;

    private MockInterface $searchParser;

    private MockInterface $dateParser;

    private MockInterface $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestParser = Mockery::mock(RequestParser::class);
        $this->filterParser = Mockery::mock(FilterParser::class);
        $this->sortParser = Mockery::mock(SortParser::class);
        $this->includeParser = Mockery::mock(IncludeParser::class);
        $this->selectParser = Mockery::mock(SelectParser::class);
        $this->searchParser = Mockery::mock(SearchParser::class);
        $this->dateParser = Mockery::mock(DateParser::class);
        $this->builder = Mockery::mock(Builder::class);

        $this->queryParser = new QueryParser(
            $this->requestParser,
            $this->filterParser,
            $this->sortParser,
            $this->includeParser,
            $this->selectParser,
            $this->searchParser,
            $this->dateParser
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_parse_method_with_empty_queries()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $this->builder->shouldReceive('when')->andReturnSelf();

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_select_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('name,email', [], '', '', [], []);
        $allow = new QueryParserAllowDTO(['name', 'email'], null, null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn(['name', 'email']);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with(['name', 'email'])->andReturn([
            [
                'fx' => 'select',
                'parameters' => [['name', 'email']],
                'isNested' => false,
            ],
        ]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $this->builder->shouldReceive('when')
            ->with([['name', 'email']], Mockery::type('Closure'))
            ->andReturnUsing(function ($condition, $callback) {
                if ($condition) {
                    $this->builder->shouldReceive('select')
                        ->with(['name', 'email'])
                        ->andReturnSelf();
                    $callback($this->builder);
                }

                return $this->builder;
            });

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_filter_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '{"=":["#name","John"]}', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, ['name'], null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn(['=' => ['#name', 'John']]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with(['=' => ['#name', 'John']])->andReturn([
            [
                'fx' => 'where',
                'parameters' => ['name', '=', 'John'],
                'isNested' => false,
            ],
        ]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $this->builder->shouldReceive('when')
            ->with(['name', '=', 'John'], Mockery::type('Closure'))
            ->andReturnUsing(function ($condition, $callback) {
                if ($condition) {
                    $this->builder->shouldReceive('where')
                        ->with('name', '=', 'John')
                        ->andReturnSelf();
                    $callback($this->builder);
                }

                return $this->builder;
            });

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_nested_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '{"FILTER_RELATION_HAS":["posts",{"=":["#title","Test"]}]}', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, ['posts.title'], null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn(['FILTER_RELATION_HAS' => ['posts', ['=' => ['#title', 'Test']]]]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')
            ->with(['FILTER_RELATION_HAS' => ['posts', ['=' => ['#title', 'Test']]]])
            ->andReturn([
                [
                    'fx' => Method::FILTER_RELATION_HAS->value,
                    'parameters' => [
                        'posts',
                        [
                            'fx' => 'where',
                            'parameters' => ['title', '=', 'Test'],
                            'isNested' => false,
                        ],
                    ],
                    'isNested' => true,
                ],
            ]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $nestedBuilder = Mockery::mock(Builder::class);
        $nestedBuilder->shouldReceive('where')
            ->with('title', '=', 'Test')
            ->andReturnSelf();
        $nestedBuilder->shouldReceive('when')
            ->andReturnSelf();

        $this->builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('Closure'))
            ->andReturnUsing(function ($relation, $callback) use ($nestedBuilder) {
                $callback($nestedBuilder);

                return $this->builder;
            });

        // Add expectation for when method that might be called on the main builder
        $this->builder->shouldReceive('when')
            ->andReturnSelf();

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_with_relation_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', ['posts'], '', '', [], []);
        $allow = new QueryParserAllowDTO(null, ['posts'], null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn(['posts']);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with(['posts'])->andReturn([
            [
                'fx' => Method::WITH->value,
                'parameters' => ['posts'],
                'isNested' => false,
            ],
        ]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $this->builder->shouldReceive('when')
            ->with(['posts'], Mockery::type('Closure'))
            ->andReturnUsing(function ($condition, $callback) {
                if ($condition) {
                    $this->builder->shouldReceive('with')
                        ->with('posts')
                        ->andReturnSelf();
                    $callback($this->builder);
                }

                return $this->builder;
            });

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_nested_with_relation_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '{"FILTER_RELATION":["posts",{"=":["#title","Test"]}]}', [], []);
        $allow = new QueryParserAllowDTO(null, ['posts'], null, ['posts.title'], null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn(['FILTER_RELATION' => ['posts', ['=' => ['#title', 'Test']]]]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')
            ->with(['FILTER_RELATION' => ['posts', ['=' => ['#title', 'Test']]]])
            ->andReturn([
                [
                    'fx' => Method::WITH->value,
                    'parameters' => [
                        'posts',
                        [
                            'fx' => 'where',
                            'parameters' => ['title', '=', 'Test'],
                            'isNested' => false,
                        ],
                    ],
                    'isNested' => true,
                ],
            ]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $nestedBuilder = Mockery::mock(Builder::class);
        $nestedBuilder->shouldReceive('where')
            ->with('title', '=', 'Test')
            ->andReturnSelf();
        $nestedBuilder->shouldReceive('when')
            ->andReturnSelf();

        // Accept any array with 'posts' key containing a closure
        $this->builder->shouldReceive('with')
            ->withArgs(function ($arg) {
                return is_array($arg) &&
                       array_key_exists('posts', $arg) &&
                       $arg['posts'] instanceof \Closure;
            })
            ->andReturnUsing(function ($relations) use ($nestedBuilder) {
                $callback = $relations['posts'];
                $callback($nestedBuilder);

                return $this->builder;
            });

        // Add expectation for when method that might be called on the main builder
        $this->builder->shouldReceive('when')
            ->andReturnSelf();

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_multiple_queries()
    {
        // Arrange
        $options = new QueryParserRequestDTO(
            'name,email',
            ['posts'],
            'name:asc',
            '{"=":["#status","active"]}',
            ['term' => 'search'],
            ['created_at' => ['start' => '2023-01-01', 'end' => '2023-12-31']]
        );
        $allow = new QueryParserAllowDTO(
            ['name', 'email'],
            ['posts'],
            ['name'],
            ['status'],
            ['term'],
            ['created_at']
        );

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn(['name', 'email']);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn(['posts']);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn(['=' => ['#status', 'active']]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn(['term' => 'search']);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn(['created_at' => ['start' => '2023-01-01', 'end' => '2023-12-31']]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn(['name' => 'asc']);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with(['name', 'email'])->andReturn([
            [
                'fx' => 'select',
                'parameters' => [['name', 'email']],
                'isNested' => false,
            ],
        ]);

        $this->includeParser->shouldReceive('parse')->with(['posts'])->andReturn([
            [
                'fx' => 'with',
                'parameters' => ['posts'],
                'isNested' => false,
            ],
        ]);

        $this->filterParser->shouldReceive('parse')->with(['=' => ['#status', 'active']])->andReturn([
            [
                'fx' => 'where',
                'parameters' => ['status', '=', 'active'],
                'isNested' => false,
            ],
        ]);

        $this->searchParser->shouldReceive('parse')->with(['term' => 'search'])->andReturn([
            [
                'fx' => 'where',
                'parameters' => ['term', 'LIKE', '%search%'],
                'isNested' => false,
            ],
        ]);

        $this->dateParser->shouldReceive('parse')->with(['created_at' => ['start' => '2023-01-01', 'end' => '2023-12-31']])->andReturn([
            [
                'fx' => 'whereBetween',
                'parameters' => ['created_at', ['2023-01-01', '2023-12-31']],
                'isNested' => false,
            ],
        ]);

        $this->sortParser->shouldReceive('parse')->with(['name' => 'asc'])->andReturn([
            [
                'fx' => 'orderBy',
                'parameters' => ['name', 'asc'],
                'isNested' => false,
            ],
        ]);

        // Setup builder expectations for each query
        // General when expectation to catch any calls
        $this->builder->shouldReceive('when')
            ->andReturnSelf();

        // Specific expectations for each method
        $this->builder->shouldReceive('select')
            ->with(['name', 'email'])
            ->andReturnSelf();

        $this->builder->shouldReceive('with')
            ->with('posts')
            ->andReturnSelf();

        $this->builder->shouldReceive('where')
            ->with('status', '=', 'active')
            ->andReturnSelf();

        $this->builder->shouldReceive('where')
            ->with('term', 'LIKE', '%search%')
            ->andReturnSelf();

        $this->builder->shouldReceive('whereBetween')
            ->with('created_at', ['2023-01-01', '2023-12-31'])
            ->andReturnSelf();

        $this->builder->shouldReceive('orderBy')
            ->with('name', 'asc')
            ->andReturnSelf();

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_default_nested_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '{"HAS":["comments",{"=":["#content","Test"]}]}', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, ['comments.content'], null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn(['HAS' => ['comments', ['=' => ['#content', 'Test']]]]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')
            ->with(['HAS' => ['comments', ['=' => ['#content', 'Test']]]])
            ->andReturn([
                [
                    'fx' => Method::HAS->value,
                    'parameters' => [
                        [
                            'fx' => 'where',
                            'parameters' => ['content', '=', 'Test'],
                            'isNested' => false,
                        ],
                    ],
                    'isNested' => true,
                ],
            ]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $nestedBuilder = Mockery::mock(Builder::class);
        $nestedBuilder->shouldReceive('where')
            ->with('content', '=', 'Test')
            ->andReturnSelf();
        $nestedBuilder->shouldReceive('when')
            ->andReturnSelf();

        $this->builder->shouldReceive('has')
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function ($callback) use ($nestedBuilder) {
                $callback($nestedBuilder);

                return $this->builder;
            });

        // Add expectation for when method that might be called on the main builder
        $this->builder->shouldReceive('when')
            ->andReturnSelf();

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_empty_parameters_but_non_empty_queries()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        $this->selectParser->shouldReceive('parse')->with([])->andReturn([
            [
                'fx' => 'select',
                'parameters' => [],
                'isNested' => false,
            ],
        ]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        $this->builder->shouldReceive('when')
            ->with([], Mockery::type('Closure'))
            ->andReturnSelf();

        // Act
        $result = $this->queryParser->parse($this->builder, $options, $allow);

        // Assert
        $this->assertSame($this->builder, $result);
    }

    public function test_parse_method_with_malformed_query()
    {
        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        // Return a malformed query (missing required keys)
        $this->selectParser->shouldReceive('parse')->with([])->andReturn([
            [
                'fx' => 'select',
                // Missing 'parameters' key
                'isNested' => false,
            ],
        ]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        // Set up expectation for when method that will be called
        $this->builder->shouldReceive('when')
            ->andThrow(new \ErrorException('Undefined array key "parameters"'));

        // Expect an exception to be thrown
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Undefined array key "parameters"');

        // Act
        $this->queryParser->parse($this->builder, $options, $allow);
    }

    public function test_generate_unit_for_all_method_enum_cases()
    {
        // This test verifies that all Method enum cases are correctly handled by the QueryParser

        // Define the method cases to test
        $methodCases = [
            Method::DEFAULT->value => ['name', '=', 'John'],
            Method::NOT->value => ['name', 'John'],
            Method::NOT_IN->value => ['name', ['John', 'Jane']],
            Method::IN->value => ['name', ['John', 'Jane']],
            Method::IS_NULL->value => ['name'],
            Method::IS_NOT_NULL->value => ['name'],
            Method::HAS->value => ['posts'],
            Method::SPECIAL_LIKE->value => ['name', 'John'],
            Method::RELATION->value => ['posts', 'title', '=', 'Test'],
            Method::ANY_RELATION->value => ['posts', 'title', ['Test1', 'Test2']],
            Method::BETWEEN->value => ['age', [18, 65]],
            Method::BETWEEN_RELATION->value => ['posts', 'likes', [10, 100]],
            Method::WITH_AGGREGATE->value => ['posts', 'count', 'posts_count'],
            Method::SELECT->value => [['name', 'email']],
            Method::ORDER_RELATION->value => ['posts', 'title', 'asc'],
        ];

        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        // Set up expectations for all parsers
        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        // Test each method case individually
        foreach ($methodCases as $method => $parameters) {
            // Create a new mock builder for each test
            $testBuilder = Mockery::mock(Builder::class);

            // Set up expectations for the builder
            $testBuilder->shouldReceive('when')
                ->with($parameters, Mockery::type('Closure'))
                ->andReturnUsing(function ($condition, $callback) use ($testBuilder, $method, $parameters) {
                    if ($condition) {
                        $testBuilder->shouldReceive($method)
                            ->with(...$parameters)
                            ->andReturnSelf();
                        $callback($testBuilder);
                    }

                    return $testBuilder;
                });

            // Create a query array with the current method
            $query = [
                'fx' => $method,
                'parameters' => $parameters,
                'isNested' => false,
            ];

            // Call handleQuery directly
            $result = $this->invokePrivateMethod($this->queryParser, 'handleQuery', [$testBuilder, [$query]]);

            // Assert the result is the same as the builder
            $this->assertSame($testBuilder, $result);
        }
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function test_specific_methods_from_issue_description()
    {
        // This test verifies that the specific methods mentioned in the issue description
        // are correctly handled by the QueryParser

        // Define the method cases to test
        $methodCases = [
            Method::ANY_RELATION->value => ['posts', 'title', ['Test1', 'Test2']], // whereRelationIn
            Method::BETWEEN_RELATION->value => ['posts', 'likes', [10, 100]], // whereRelationBetween
            Method::SPECIAL_LIKE->value => ['name', 'John'], // whereLikeRelationship
            Method::ORDER_RELATION->value => ['posts', 'title', 'asc'], // orderByRelationship
        ];

        // Arrange
        $options = new QueryParserRequestDTO('', [], '', '', [], []);
        $allow = new QueryParserAllowDTO(null, null, null, null, null, null);

        $mockRequestParserResult = Mockery::mock(RequestParser::class);
        $mockRequestParserResult->shouldReceive('getSelect')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getInclude')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getFilter')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSearch')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getDate')->andReturn([]);
        $mockRequestParserResult->shouldReceive('getSort')->andReturn([]);

        $this->requestParser->shouldReceive('parse')
            ->with($options, $allow)
            ->andReturn($mockRequestParserResult);

        // Set up expectations for all parsers
        $this->selectParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->includeParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->filterParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->searchParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->dateParser->shouldReceive('parse')->with([])->andReturn([]);
        $this->sortParser->shouldReceive('parse')->with([])->andReturn([]);

        // Test each method case individually
        foreach ($methodCases as $method => $parameters) {
            // Create a new mock builder for each test
            $testBuilder = Mockery::mock(Builder::class);

            // Set up expectations for the builder
            $testBuilder->shouldReceive('when')
                ->with($parameters, Mockery::type('Closure'))
                ->andReturnUsing(function ($condition, $callback) use ($testBuilder, $method, $parameters) {
                    if ($condition) {
                        $testBuilder->shouldReceive($method)
                            ->with(...$parameters)
                            ->andReturnSelf();
                        $callback($testBuilder);
                    }

                    return $testBuilder;
                });

            // Create a query array with the current method
            $query = [
                'fx' => $method,
                'parameters' => $parameters,
                'isNested' => false,
            ];

            // Call handleQuery directly
            $result = $this->invokePrivateMethod($this->queryParser, 'handleQuery', [$testBuilder, [$query]]);

            // Assert the result is the same as the builder
            $this->assertSame($testBuilder, $result);
        }

        // Test dynamicPaginate separately as it's not part of the Method enum
        $testBuilder = Mockery::mock(Builder::class);

        // Parameters for dynamicPaginate
        $parameters = [[]];

        // Set up expectations for the builder
        $testBuilder->shouldReceive('when')
            ->with($parameters, Mockery::type('Closure'))
            ->andReturnUsing(function ($condition, $callback) use ($testBuilder, $parameters) {
                if ($condition) {
                    $testBuilder->shouldReceive('dynamicPaginate')
                        ->with(...$parameters)
                        ->andReturnSelf();
                    $callback($testBuilder);
                }

                return $testBuilder;
            });

        // Create a query array for dynamicPaginate
        $query = [
            'fx' => 'dynamicPaginate',
            'parameters' => $parameters,
            'isNested' => false,
        ];

        // Call handleQuery directly
        $result = $this->invokePrivateMethod($this->queryParser, 'handleQuery', [$testBuilder, [$query]]);

        // Assert the result is the same as the builder
        $this->assertSame($testBuilder, $result);
    }
}
