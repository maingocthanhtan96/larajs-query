<?php

namespace LaraJS\QueryParser\RequestParser;

use Exception;
use Illuminate\Support\Arr;

class FilterParser implements FilterParserInterface
{
    public function __construct(private readonly IbmParser $ibmParser)
    {
    }

    /**
     * @throws Exception
     */
    public function parse(string|array $queryString): array
    {
        if (!$queryString) {
            return [];
        }
        return $this->ibmParser->parse(Arr::wrap($queryString));
    }
}
