<?php

namespace LaraJS\QueryParser\QueryParser;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaraJS\QueryParser\Enum\Method;
use LaraJS\QueryParser\RequestParser\RequestParser;
use Illuminate\Support\Arr;

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
     * @throws Exception
     */
    public function parse(Builder $query, Request $request, array $option): Builder
    {
        $requestParser = $this->requestParser->parse($request, $option);
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
            if ($d['isNested']) {
                switch ($d['fx']) {
                    case Method::HAS->value:
                        $parameters = $d['parameters'][1];
                        $parameters = Arr::isAssoc($parameters) ? [$parameters] : $parameters;
                        $query->{$d['fx']}(
                            $d['parameters'][0],
                            fn(Builder $query) => $this->handleQuery($query, $parameters),
                        );
                        break;
                    default:
                        $query->{$d['fx']}(fn(Builder $query) => $this->handleQuery($query, $d['parameters']));
                }
            } else {
                switch ($d['fx']) {
                    case Method::SPECIAL_LIKE->value:
                        $query->when(
                            $d['parameters'][0] && $d['parameters'][1],
                            fn(Builder $q) => $query->{$d['fx']}(...$d['parameters']),
                        );
                        break;
                    default:
                        $query->when($d['parameters'], fn(Builder $q) => $query->{$d['fx']}(...$d['parameters']));
                }
            }
        }

        return $query;
    }
}
