<?php

namespace LaraJS\Query\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RequestParser implements RequestParserInterface
{
    protected string|array $filter;

    protected array $include;

    protected array $search;

    protected array $date;

    protected array $sort;

    protected array $select;

    public function __construct(
        private readonly FilterParser $filterParser,
        private readonly SortParser $sortParser,
        private readonly IncludeParser $includeParser,
        private readonly FieldParser $fieldParser,
        private readonly SearchParser $searchParser,
        private readonly DateParser $dateParser,
    ) {}

    /**
     * @param  Builder  $query
     * @param  Request  $request
     * @return $this
     */
    public function parse(Builder $query, Request $request): RequestParser
    {
        $option = $this->parseOption($request);
        $this->setInclude($this->includeParser->parse($query, $option['include']))
            ->setFilter($this->filterParser->parse($query, $option['filter']))
            ->setSort($this->sortParser->parse($query, $option['sort']))
            ->setSearch($this->searchParser->parse($query, $option['search']))
            ->setDate($this->dateParser->parse($query, $option['date']))
            ->setSelect($this->fieldParser->parse($query, $option['select']));

        return $this;
    }

    public function parseOption(Request $request): array
    {
        $parseOptions['include'] = $request->query('include', []);
        $parseOptions['search'] = $request->query('search', []);
        $parseOptions['date'] = $request->query('date', []);
        $parseOptions['filter'] = $request->query('filter', []);
        $parseOptions['select'] = $request->query('select', '');
        $parseOptions['sort'] = $request->query('sort', '');

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
     * @param  array  $include
     * @return RequestParser
     */
    public function setInclude(array $include): RequestParser
    {
        $this->include = $include;

        return $this;
    }

    /**
     * @return string|array
     */
    public function getFilter(): string|array
    {
        return $this->filter;
    }

    /**
     * @param  string|array  $filter
     * @return RequestParser
     */
    public function setFilter(string|array $filter): RequestParser
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
     * @param  array  $search
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
     * @param  array  $date
     * @return RequestParser
     */
    public function setDate(array $date): RequestParser
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @param  array  $select
     * @return RequestParser
     */
    public function setSelect(array $select): RequestParser
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
     * @param  array  $sort
     * @return RequestParser
     */
    public function setSort(array $sort): RequestParser
    {
        $this->sort = $sort;

        return $this;
    }
}
