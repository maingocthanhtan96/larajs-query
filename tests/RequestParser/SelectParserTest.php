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

    public function test_parser()
    {
        $queryString = 'id,name,email,';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_no_query_parser()
    {
        $queryString = '';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, $expect));
    }

    public function test_query_inside_filterable_query_parser()
    {
        $queryString = 'id';
        $expect = ['id'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['id', 'name', 'email']));
    }

    public function test_parser_empty_filterable()
    {
        $queryString = 'id,name,email,';
        $expect = [];

        $this->assertSame($expect, $this->parser->parse($queryString, $expect));
    }

    public function test_parser_filterable()
    {
        $queryString = 'id,name,age,';
        $expect = ['id', 'name', 'age'];

        $this->assertSame($expect, $this->parser->parse($queryString, $expect));
    }

    public function test_parser_invalid_filterable()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queryString = 'id,name,age,';

        $this->parser->parse($queryString, ['id', 'name']);
    }

    public function test_parser_with_whitespace()
    {
        $queryString = 'id ,  name  ,   email';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_with_duplicate_fields()
    {
        $queryString = 'id,name,id,email,name';
        $expect = ['id', 'name', 'id', 'email', 'name'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_with_empty_fields()
    {
        $queryString = 'id,,name,,email';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_with_multiple_consecutive_commas()
    {
        $queryString = 'id,,,name,,,,email,,,';
        $expect = ['id', 'name', 'email'];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_with_null_filterable_and_empty_string()
    {
        $queryString = '';
        $expect = [];

        $this->assertSame($expect, $this->parser->parse($queryString, null));
    }

    public function test_parser_with_filterable_and_spaced_fields()
    {
        $queryString = 'user_id, user_name, email_address';
        $filterable = ['user_id', 'user_name', 'email_address'];
        $expect = ['user_id', 'user_name', 'email_address'];

        $this->assertSame($expect, $this->parser->parse($queryString, $filterable));
    }
}
