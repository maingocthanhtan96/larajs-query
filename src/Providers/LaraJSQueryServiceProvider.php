<?php

namespace LaraJS\Query\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LaraJS\Query\Macros\CollectionPaginateMacro;
use LaraJS\Query\Macros\DynamicPaginateMacro;
use LaraJS\Query\Macros\OrderByRelationshipMacro;
use LaraJS\Query\Macros\WhereLikeRelationshipMacro;
use LaraJS\Query\Macros\WhereRelationBetweenMacro;
use LaraJS\Query\Macros\WhereRelationInMacro;
use LaraJS\Query\QueryParser\DateParser;
use LaraJS\Query\QueryParser\FilterParser;
use LaraJS\Query\QueryParser\IncludeParser;
use LaraJS\Query\QueryParser\QueryParser;
use LaraJS\Query\QueryParser\QueryParserInterface;
use LaraJS\Query\QueryParser\SearchParser;
use LaraJS\Query\QueryParser\SelectParser;
use LaraJS\Query\QueryParser\SortParser;
use LaraJS\Query\RequestParser\RequestParser;

class LaraJSQueryServiceProvider extends ServiceProvider
{
    protected bool $defer = false;

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../../config/larajs-query.php' => config_path('larajs-query.php'),
            ],
            'larajs-query',
        );
        $this->mergeConfigFrom(__DIR__ . '/../../config/larajs-query.php', 'larajs-query');
    }

    public function register(): void
    {
        $this->app->singleton(QueryParserInterface::class, function (Application $app) {
            return new QueryParser(
                $app->make(RequestParser::class),
                $app->make(FilterParser::class),
                $app->make(SortParser::class),
                $app->make(IncludeParser::class),
                $app->make(SelectParser::class),
                $app->make(SearchParser::class),
                $app->make(DateParser::class),
            );
        });

        WhereRelationInMacro::register();
        WhereRelationBetweenMacro::register();
        WhereLikeRelationshipMacro::register();
        CollectionPaginateMacro::register();
        OrderByRelationshipMacro::register();
        DynamicPaginateMacro::register();
    }
}
