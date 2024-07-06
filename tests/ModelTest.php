<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;

class ModelTest extends Model
{
    public function allowQueryParsers(): array
    {
        return [
            'field' => ['id', 'name', 'email'],
            'include' => ['roles'],
            'sort' => ['id', 'updated_at'],
            'filter' => ['name', 'age', 'lastModified', 'duration', 'percentage', 'description', 'chapter', 'lastName', 'articles', 'orders', 'invoices'],
            'search' => ['id', 'name', 'roles'],
            'date' => ['updated_at'],
        ];
    }
}
