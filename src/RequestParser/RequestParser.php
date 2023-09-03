<?php

namespace LaraJS\QueryParser\RequestParser;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RequestParser implements RequestParserInterface
{
    protected array $include;
    protected array $filter;
    protected array $search;
    protected array $date;
    protected array $sort;
    protected string $select;

    public function __construct(private readonly FilterParser $filterParser, private readonly SortParser $sortParser, private readonly IncludeParser $includeParser)
    {
    }

    /**
     * @throws Exception
     */
    public function parse(Request $request, array $option): RequestParser
    {
        $option = $this->parseOption($request, $option);
        $this->setInclude($this->includeParser->parse($option['include']))
            ->setFilter($this->filterParser->parse(Arr::wrap($option['filter'])))
            ->setSort($this->sortParser->parse($option['orderBy']))
            ->setSearch($option['search'])
            ->setDate($option['date'])
            ->setSelect($option['select']);

        return $this;
    }

    public function parseOption(Request $request, array $option): array
    {
        $parseOptions['include'] = $option['include'] ?? $request->get('include', []);
        $parseOptions['filter'] = $option['filter'] ?? $request->get('filter', []);
        $parseOptions['search'] = $option['search'] ?? $request->get('search', []);
        $parseOptions['date'] = $option['date'] ?? $request->get('date', []);
        $parseOptions['select'] = $option['select'] ?? $request->get('select', '');
        $parseOptions['orderBy'] = $option['orderBy'] ?? $request->get('orderBy', '');

        return $parseOptions;
    }

    /**
     * @return array
     */
    public function getInclude(): array
    {
        return $this->include;
    }

    /**
     * @param array $include
     * @return RequestParser
     */
    public function setInclude(array $include): RequestParser
    {
        $this->include = $include;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     * @return RequestParser
     */
    public function setFilter(array $filter): RequestParser
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return array
     */
    public function getSearch(): array
    {
        return $this->search;
    }

    /**
     * @param array $search
     * @return RequestParser
     */
    public function setSearch(array $search): RequestParser
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return array
     */
    public function getDate(): array
    {
        return $this->date;
    }

    /**
     * @param array $date
     * @return RequestParser
     */
    public function setDate(array $date): RequestParser
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelect(): string
    {
        return $this->select;
    }

    /**
     * @param string $select
     * @return RequestParser
     */
    public function setSelect(string $select): RequestParser
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     * @return RequestParser
     */
    public function setSort(array $sort): RequestParser
    {
        $this->sort = $sort;
        return $this;
    }
}
