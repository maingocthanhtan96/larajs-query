<?php

namespace LaraJS\QueryParser\RequestParser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FieldParser implements FieldParserInterface
{
    /**
     * @throws \Throwable
     */
    public function parse(Builder $query, string $queryString): array
    {
        if (!$queryString) {
            return [];
        }
        $filterable = method_exists($query->getModel(), 'getFilterable')
            ? $query->getModel()->getFilterable()
            : [];

        $fields = Str::of($queryString)
            ->trim(',')
            ->explode(',')
            ->map(fn ($value) => trim($value))
            ->all();

        if ($filterable) {
            $invalidFields = array_diff($fields, $filterable);
            if ($invalidFields) {
                throw new HttpException(400, 'The select field must contain only the allowed fields.');
            }
        }

        return $fields;
    }
}
