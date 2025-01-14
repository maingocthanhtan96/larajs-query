<?php

namespace LaraJS\Query\RequestParser;

use InvalidArgumentException;

class IncludeParser
{
    /**
     * Summary of parse
     *
     * @param  array  $queryString
     * @param  ?array<string>  $filterable
     * @return array
     */
    public function parse(array $queryString, ?array $filterable): array
    {
        if (is_null($filterable)) {
            return $queryString;
        }

        if (!$queryString || !$filterable) {
            return [];
        }

        $filterableMap = $this->buildFilterableMap($filterable);

        $parsedArray = [];

        foreach ($queryString as $aggregate) {
            [$relation, $fields] = explode(':', $aggregate, 2) + [null, null];

            if (str_contains($relation, '|')) {
                if (!in_array($relation, $filterable, true)) {
                    throw new InvalidArgumentException("Relations: '{$relation}' is not allowed");
                }
                $parsedArray[] = $aggregate;

                continue;
            }

            if (!isset($filterableMap[$relation])) {
                throw new InvalidArgumentException("Relations: '{$relation}'is not allowed");
            }

            [, $allowedFields] = explode(':', $filterableMap[$relation], 2) + [null, null];
            $allowedFieldsArray = $allowedFields ? explode(',', $allowedFields) : [];

            if ($fields) {
                $this->validateFields($fields, $allowedFieldsArray, $relation);
                $parsedArray[] = $aggregate;
            } else {
                $parsedArray[] = $filterableMap[$relation];
            }
        }

        return $parsedArray;
    }

    /**
     * Build a filterable map from the filterable array.
     *
     * @param  array<string>  $filterable
     * @return array<string, array<string>>
     */
    private function buildFilterableMap(array $filterable): array
    {
        $filterableMap = [];
        foreach ($filterable as $item) {
            [$relation] = explode(':', $item, 2);
            if ($relation) {
                $filterableMap[$relation] = $item;
            }
        }

        return $filterableMap;
    }

    /**
     * Validate fields against allowed fields for a given relation.
     *
     * @param  string  $fields
     * @param  array<string>  $allowedFields
     * @param  string  $relation
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function validateFields(string $fields, array $allowedFields, string $relation): void
    {
        $requestedFields = explode(',', $fields);
        foreach ($requestedFields as $field) {
            if (!in_array($field, $allowedFields, true)) {
                throw new InvalidArgumentException(
                    "Field '{$field}' is not allowed for relation '{$relation}'."
                );
            }
        }
    }
}
