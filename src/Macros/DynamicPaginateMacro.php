<?php

namespace LaraJS\Query\Macros;

use Illuminate\Database\Eloquent\Builder;

class DynamicPaginateMacro
{
    public static function register(): void
    {
        if (!Builder::hasGlobalMacro('dynamicPaginate')) {
            Builder::macro('dynamicPaginate', function (array $options = []) {
                $request = request();
                $defaultLimit = $options['limit']['default'] ?? config('larajs-query.limit.default', 25);
                $maxLimit = $options['limit']['max'] ?? config('larajs-query.limit.max', 500);
                $limit = min($request->input('pagination.limit', $defaultLimit), $maxLimit);

                if ($request->input('pagination.page') === '-1') {
                    return $this->take($limit)->get();
                }

                $paginationType = $request->input('pagination.type');

                if ($paginationType === 'cursor') {
                    return $this->cursorPaginate(perPage: $limit, cursorName: 'pagination.cursor')
                        ->setCursorName('pagination[cursor]')
                        ->appends($request->except('pagination.cursor'));
                }

                $paginator = $paginationType === 'simple'
                    ? $this->simplePaginate(perPage: $limit, pageName: 'pagination.page')
                    : $this->paginate(perPage: $limit, pageName: 'pagination.page');

                return $paginator
                    ->setPageName('pagination[page]')
                    ->appends($request->except('pagination.page'));
            });
        }
    }
}
