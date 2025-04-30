<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;
use LaraJS\Query\Enum\Method;
use LaraJS\Query\RequestParser\RequestParser;

class QueryParser implements QueryParserInterface
{
    public function __construct(
        private readonly RequestParser $requestParser,
        private readonly FilterParser $filterParser,
        private readonly SortParser $sortParser,
        private readonly IncludeParser $aggregateParser,
        private readonly SelectParser $selectParser,
        private readonly SearchParser $searchParser,
        private readonly DateParser $dateParser,
    ) {}

    /**
     * @param  Builder  $query
     * @param  QueryParserRequestDTO  $options
     * @param  QueryParserAllowDTO  $allow
     * @return Builder
     */
    public function parse(Builder $query, QueryParserRequestDTO $options, QueryParserAllowDTO $allow): Builder
    {
        $requestParser = $this->requestParser->parse($options, $allow);

        $queries = array_merge(
            $this->selectParser->parse($requestParser->getSelect()),
            $this->aggregateParser->parse($requestParser->getInclude()),
            $this->filterParser->parse($requestParser->getFilter()),
            $this->searchParser->parse($requestParser->getSearch()),
            $this->dateParser->parse($requestParser->getDate()),
            $this->sortParser->parse($requestParser->getSort())
        );

        return $this->handleQuery($query, $queries);
    }

    private function handleQuery(Builder $builder, array $queries): Builder
    {
        foreach ($queries as $query) {
            $fx = $query['fx'];
            $parameters = $query['parameters'];

            if ($query['isNested']) {
                // NOTE: maybe apply for whereHas
                // $builder->{$fx}($parameters[0], fn(Builder $query) => $this->handleQuery($query, $this->getNestedParameters($parameters)));
                $builder->{$fx}(fn(Builder $q) => $this->handleQuery($q, $parameters));
            } else {
                $builder->when($parameters, fn(Builder $q) => $q->{$fx}(...$parameters));
            }
        }

        return $builder;
    }

    //    private function getNestedParameters(array $parameters): array
    //    {
    //        return Arr::isAssoc($parameters[1]) ? [$parameters[1]] : $parameters[1];
    //    }
}
