<?php

namespace LaraJS\Query\DTO;

class QueryParserAllowDTO
{
    /**
     * @param  array<string>|null  $select
     * @param  array<string>|null  $include
     * @param  array<string>|null  $sort
     * @param  array<string>|null  $filter
     * @param  array<string>|null  $search
     * @param  array<string>|null  $date
     */
    public function __construct(
        public ?array $select,
        public ?array $include,
        public ?array $sort,
        public ?array $filter,
        public ?array $search,
        public ?array $date
    ) {}

    /**
     * @param  array{select?: array<string>, include?: array<string>, sort?: array<string>, filter?: array<string>, search?: array<string>, date?: array<string>}  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['select'] ?? null,
            $data['include'] ?? null,
            $data['sort'] ?? null,
            $data['filter'] ?? null,
            $data['search'] ?? null,
            $data['date'] ?? null
        );
    }
}
