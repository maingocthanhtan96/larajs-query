<?php

namespace Tests\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use LaraJS\Query\RequestParser\SelectParser;
use PHPUnit\Framework\TestCase;

class SelectParserTest extends TestCase
{
    private SelectParser $parser;

    private Builder $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SelectParser;
    }

    public function testParser()
    {
        $queryString = 'id,name,email,';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testNoQueryParser()
    {
        $queryString = '';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, $expect));
    }

    public function testQueryInsideFilterableQueryParser()
    {
        $queryString = 'id';
        $expect = ['id'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['id', 'name', 'email']));
    }

    public function testParserEmptyFilterable()
    {
        $queryString = 'id,name,email,';
        $expect = [];

        $this->assertSame($expect, $this->parser->parse($queryString, $expect));
    }

    public function testParserFilterable()
    {
        $queryString = 'id,name,age,';
        $expect = ['id', 'name', 'age'];

        $this->assertSame($expect, $this->parser->parse($queryString, $expect));
    }

    public function testParserInvalidFilterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = 'id,name,age,';

        $this->parser->parse($queryString, ['id', 'name']);
    }

    public function testParserWithWhitespace()
    {
        $queryString = 'id ,  name  ,   email';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserWithDuplicateFields()
    {
        $queryString = 'id,name,id,email,name';
        $expect = ['id', 'name', 'id', 'email', 'name'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserWithEmptyFields()
    {
        $queryString = 'id,,name,,email';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserWithMultipleConsecutiveCommas()
    {
        $queryString = 'id,,,name,,,,email,,,';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserWithNullFilterableAndEmptyString()
    {
        $queryString = '';
        $expect = [];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function testParserWithFilterableAndSpacedFields()
    {
        $queryString = 'user_id, user_name, email_address';
        $filterable = ['user_id', 'user_name', 'email_address'];
        $expect = ['user_id', 'user_name', 'email_address'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }
}
