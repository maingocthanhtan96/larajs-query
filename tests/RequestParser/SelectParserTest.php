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

        $this->assertSame($expect, $this->parser->parse($queryString, []));
    }

    public function testParserFilterable()
    {
        $queryString = 'id,name,age,';
        $expect = ['id', 'name'];

        $this->assertSame($expect, $this->parser->parse($queryString, ['id', 'name']));
    }
}
