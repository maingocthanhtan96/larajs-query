<?php

namespace LaraJS\Query\QueryParser;

use Illuminate\Support\Str;

class IncludeParser
{
    /**
     * @param  array{with: array, withWhereHas: array}  $aggregates
     * @return array
     */
    public function parse(array $aggregates): array
    {
        $fxWith = array_map(fn($aggregate) => $this->parseAggregate($aggregate), $aggregates['with'] ?? []);
        $fxWithWhereHas = (new FilterParser)->parse($aggregates['withWhereHas'] ?? []);

        return array_merge($this->mergeWithFx($fxWith), $fxWithWhereHas);
    }

    private function parseAggregate(string $aggregate): array
    {
        if (Str::contains($aggregate, '|')) {
            [$relationColumn, $method] = explode('|', $aggregate);
            [$relation, $column] = explode('.', $relationColumn) + [null, null];
            $method = strtolower($method);

            return [
                'fx' => 'withAggregate',
                'isNested' => false,
                'parameters' => [
                    $relation,
                    in_array($method, ['count', 'exists'], true) ? '*' : $column,
                    $method,
                ],
            ];
        }

        return [
            'fx' => 'with',
            'isNested' => false,
            'parameters' => [$aggregate],
        ];
    }

    private function mergeWithFx(array $parsedArray): array
    {
        $mergedArray = [];

        foreach ($parsedArray as $item) {
            if ($item['fx'] === 'with') {
                $mergedArray['with'] = $this->mergeWithParameters(
                    $mergedArray['with'] ?? ['fx' => 'with', 'isNested' => false, 'parameters' => []],
                    $item
                );
            } else {
                $mergedArray[] = $item;
            }
        }

        return array_values($mergedArray);
    }

    private function mergeWithParameters(array $existingItem, array $newItem): array
    {
        $existingItem['parameters'] = array_merge($existingItem['parameters'], $newItem['parameters']);

        return $existingItem;
    }
}
