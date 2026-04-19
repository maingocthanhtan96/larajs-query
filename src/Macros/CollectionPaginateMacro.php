<?php

namespace LaraJS\Query\Macros;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class CollectionPaginateMacro
{
    public static function register(): void
    {
        if (!Collection::hasMacro('collectionPaginate')) {
            Collection::macro('collectionPaginate', function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage)->values()->all(),
                    $this->count(),
                    $perPage,
                    $page,
                    $options,
                ))->withPath(Request::url());
            });
        }
    }
}
