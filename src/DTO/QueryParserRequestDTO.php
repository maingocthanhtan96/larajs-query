<?php

namespace LaraJS\Query\DTO;

class QueryParserRequestDTO
{
    /**
     * @param  string  $select
     * @param  array<string>  $include
     * @param  string  $sort
     * @param  array<string>  $filter
     * @param  array<string>  $search
     * @param  array<string>  $date
     */
    public function __construct(
        public string $select,
        public array $include,
        public string $sort,
        public string|array $filter,
        public array $search,
        public array $date
    ) {}

    /**
     * @param  array{select?: string, include?: array<string>, sort?: string, filter?: array<string>, search?: array<string>, date?: array<string>}  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['select'] ?? '',
            $data['include'] ?? [],
            $data['sort'] ?? '',
            $data['filter'] ?? [],
            $data['search'] ?? [],
            $data['date'] ?? []
        );
    }
}
