<?php

namespace LaraJS\Query\RequestParser;

class RequestParser
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
        private readonly SelectParser $fieldParser,
        private readonly SearchParser $searchParser,
        private readonly DateParser $dateParser,
    ) {}

    /**
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $options
     * @param  array{select:  array<string>, include: array<string>, sort: array<string>, filter: array<string>, search: array<string>, date: array<string>}  $allows
     * @return $this
     */
    public function parse(array $options, array $allows): RequestParser
    {
        $this->setInclude($this->includeParser->parse($options['include'] ?? [], $allows['include'] ?? []))
            ->setFilter($this->filterParser->parse($options['filter'] ?? [], $allows['filter'] ?? []))
            ->setSearch($this->searchParser->parse($options['search'] ?? [], $allows['search'] ?? []))
            ->setDate($this->dateParser->parse($options['date'] ?? [], $allows['date'] ?? []))
            ->setSort($this->sortParser->parse($options['sort'] ?? '', $allows['sort'] ?? []))
            ->setSelect($this->fieldParser->parse($options['select'] ?? '', $allows['select'] ?? []));

        return $this;
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
