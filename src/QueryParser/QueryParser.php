<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Database\Eloquent\Builder;
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

    public function parse(Builder $query, QueryParserRequestDTO $options, QueryParserAllowDTO $allow): Builder
    {
        $requestParser = $this->requestParser->parse($options, $allow);

        $queries = [
            ...$this->selectParser->parse($requestParser->select),
            ...$this->includeParser->parse($requestParser->include),
            ...$this->filterParser->parse($requestParser->filter),
            ...$this->searchParser->parse($requestParser->search),
            ...$this->dateParser->parse($requestParser->date),
            ...$this->sortParser->parse($requestParser->sort),
        ];

        return $this->handleQuery($query, $queries);
    }

    private function handleQuery(Builder $builder, array $queries): Builder
    {
        foreach ($queries as ['fx' => $fx, 'parameters' => $parameters, 'isNested' => $isNested]) {
            if ($isNested) {
                match ($fx) {
                    Method::WITH->value => $builder->{$fx}([$parameters[0] => fn($q) => $this->handleQuery($q, [$parameters[1]])]),
                    Method::FILTER_RELATION_HAS->value => $builder->{$fx}($parameters[0], fn($q) => $this->handleQuery($q, [$parameters[1]])),
                    default => $builder->{$fx}(fn(Builder $q) => $this->handleQuery($q, $parameters))
                };

                continue;
            }

            $builder->when($parameters, fn(Builder $q) => $q->{$fx}(...$parameters));
        }

        return $builder;
    }
}
