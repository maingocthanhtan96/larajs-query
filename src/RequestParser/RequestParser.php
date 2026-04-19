<?php

namespace LaraJS\Query\RequestParser;

use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;

class RequestParser
{
    public string|array $filter = [];

    public array $include = [];

    public array $search = [];

    public array $date = [];

    public array $sort = [];

    public array $select = [];

    public function __construct(
        private readonly FilterParser $filterParser,
        private readonly SortParser $sortParser,
        private readonly IncludeParser $includeParser,
        private readonly SelectParser $selectParser,
        private readonly SearchParser $searchParser,
        private readonly DateParser $dateParser,
    ) {}

    public function parse(QueryParserRequestDTO $options, QueryParserAllowDTO $allow): self
    {
        $this->include = $this->includeParser->parse($options->include ?? [], $allow->include);
        $this->filter = $this->filterParser->parse($options->filter ?? [], $allow->filter);
        $this->search = $this->searchParser->parse($options->search ?? [], $allow->search);
        $this->date = $this->dateParser->parse($options->date ?? [], $allow->date);
        $this->sort = $this->sortParser->parse($options->sort ?? '', $allow->sort);
        $this->select = $this->selectParser->parse($options->select ?? '', $allow->select);

        return $this;
    }
}
