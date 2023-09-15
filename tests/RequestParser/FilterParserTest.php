<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaraJS\QueryParser\LaraJSQueryParser;
use LaraJS\QueryParser\QueryParser\QueryParser;
use LaraJS\QueryParser\RequestParser\RequestParser;
use PHPUnit\Framework\TestCase;
use Mockery;

class FilterParserTest extends TestCase
{
    use LaraJSQueryParser;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function testFilterParser()
    {
        $queryString = [
            'filter' => "equals(email,'larajs@gmail.com')"
        ];
        $options = [];
        $mock = Mockery::mock(Builder::class);
        $request = new Request($queryString);
        $requestParser = Mockery::spy(RequestParser::class);
        $queryParser = Mockery::mock(QueryParser::class);
        $modelMock = Mockery::mock(Model::class);
        $modelMock->shouldReceive('getTable')->once()->andReturn('user');
        $modelMock->shouldReceive('query')->once();
//        $requestParser = $requestParser->shouldReceive('parse')->with($request, $options)->once()->andReturnSelf();
//        $queryParser->shouldReceive('parse')->with($modelMock, $queryParser)->once();
//        dd($requestParser->getFilter());
//        $requestParser->parse($request, $options);
        $requestParser->setFilter('12321');
        $this->assertInstanceOf(Builder::class, $this->applyQueryBuilder($mock, $request));
    }
}
