<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Http\Request;

interface RequestParserInterface
{
    public function parse(Request $request, array $option): RequestParser;

    public function parseOption(Request $request, array $option): array;
}
