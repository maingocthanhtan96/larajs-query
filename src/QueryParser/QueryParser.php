<?php

namespace LaraJS\QueryParser\QueryParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LaraJS\QueryParser\Enum\Method;
use LaraJS\QueryParser\RequestParser\RequestParser;

class QueryParser implements QueryParserInterface
{
    public function __construct(
        private readonly RequestParser $requestParser,
        private readonly FilterParser $filterParser,
        private readonly SortParser $sortParser,
        private readonly IncludeParser $aggregateParser,
        private readonly FieldParser $fieldParser,
        private readonly SearchParser $searchParser,
        private readonly DateParser $dateParser,
    ) {
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     * @return Builder
     */
    public function parse(Builder $query, Request $request): Builder
    {
        $requestParser = $this->requestParser->parse($query, $request);
        $field = $this->fieldParser->parse($requestParser->getSelect());
        $search = $this->searchParser->parse($requestParser->getSearch());
        $date = $this->dateParser->parse($requestParser->getDate());
        $include = $this->aggregateParser->parse($requestParser->getInclude());
        $filter = $this->filterParser->parse($requestParser->getFilter());
        $sort = $this->sortParser->parse($requestParser->getSort());
        $data = [...$field, ...$include, ...$filter, ...$search, ...$date, ...$sort];

        return $this->handleQuery($query, $data);
    }

    private function handleQuery(Builder $query, array $data): Builder
    {
        foreach ($data as $d) {
            $fx = $d['fx'];
            $parameters = $d['parameters'];

            if ($d['isNested']) {
                $parameters = Arr::isAssoc($parameters[1]) ? [$parameters[1]] : $parameters[1];
                $query->{$fx}(
                    $parameters[0],
                    fn(Builder $q) => $this->handleQuery($q, $parameters)
                );
            } else {
                $query->when($parameters, fn(Builder $q) => $this->applyFunction($q, $fx, $parameters));
            }
        }

        return $query;
    }

    private function applyFunction(Builder $query, string $fx, array $parameters): void
    {
        if ($fx === Method::SPECIAL_LIKE->value) {
            $query->when(
                $parameters[0] && $parameters[1],
                fn(Builder $q) => $q->{$fx}(...$parameters)
            );
        } else {
            $query->{$fx}(...$parameters);
        }
    }
}
