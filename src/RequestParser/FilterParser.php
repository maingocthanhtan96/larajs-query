<?php

namespace LaraJS\QueryParser\RequestParser;

use Exception;

class FilterParser implements FilterParserInterface
{
    public function __construct(private readonly IbmParser $ibmParser)
    {
    }

    /**
     * @throws Exception
     */
    public function parse(array $queryString): array
    {
        return $this->ibmParser->parse($queryString);
    }
}
