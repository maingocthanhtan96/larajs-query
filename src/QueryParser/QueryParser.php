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
        private readonly IncludeParser $includeParser,
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
            $this->includeParser->parse($requestParser->getInclude()),
            $this->filterParser->parse($requestParser->getFilter()),
            $this->searchParser->parse($requestParser->getSearch()),
            $this->dateParser->parse($requestParser->getDate()),
            $this->sortParser->parse($requestParser->getSort())
        );

        return $this->handleQuery($query, $queries);
    }

    private function handleQuery($builder, array $queries)
    {
        foreach ($queries as $query) {
            $fx = $query['fx'];
            $parameters = $query['parameters'];

            if ($query['isNested']) {
                switch ($fx) {
                    case Method::WITH->value:
                        $builder->{$fx}([$parameters[0] => fn($q) => $this->handleQuery($q, [$parameters[1]])]);
                        break;
                    case Method::FILTER_RELATION_HAS->value:
                        $builder->{$fx}($parameters[0], fn($q) => $this->handleQuery($q, [$parameters[1]]));
                        break;
                    default:
                        $builder->{$fx}(fn(Builder $q) => $this->handleQuery($q, $parameters));
                }
            } else {
                $builder->when($parameters, fn(Builder $q) => $q->{$fx}(...$parameters));
            }
        }

        return $builder;
    }
}
