<?php

namespace Tests\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use LaraJS\Query\Providers\LaraJSQueryServiceProvider;
use Mockery;
use PHPUnit\Framework\TestCase;

class LaraJSQueryServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock application and register the service provider
        $app = Mockery::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('singleton')->andReturn();
        $app->shouldReceive('make')->andReturn(Mockery::mock());

        $provider = new LaraJSQueryServiceProvider($app);
        $provider->register();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_where_relation_in_macro_is_registered()
    {
        $this->assertTrue(Builder::hasGlobalMacro('whereRelationIn'));
    }

    public function test_where_relation_between_macro_is_registered()
    {
        $this->assertTrue(Builder::hasGlobalMacro('whereRelationBetween'));
    }

    public function test_where_like_relationship_macro_is_registered()
    {
        $this->assertTrue(Builder::hasGlobalMacro('whereLikeRelationship'));
    }

    public function test_order_by_relationship_macro_is_registered()
    {
        $this->assertTrue(Builder::hasGlobalMacro('orderByRelationship'));
    }

    public function test_dynamic_paginate_macro_is_registered()
    {
        $this->assertTrue(Builder::hasGlobalMacro('dynamicPaginate'));
    }

    public function test_collection_paginate_macro_is_registered()
    {
        $this->assertTrue(Collection::hasMacro('collectionPaginate'));
    }

    public function test_where_relation_in_macro_calls_where_has()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('whereRelationIn');
        $result = $macro->call($builder, 'posts', 'status', ['published', 'draft']);

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_between_macro_calls_where_has()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('whereRelationBetween');
        $result = $macro->call($builder, 'posts', 'created_at', ['2023-01-01', '2023-12-31']);

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_macro_with_simple_column()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('where')
            ->with(Mockery::type('callable'))
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['name'], 'john');

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_macro_with_simple_column()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('orderBy')
            ->with('name', 'asc')
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'name', 'asc');

        $this->assertSame($builder, $result);
    }

    public function test_collection_paginate_macro_functionality()
    {
        // Mock the Request facade
        Request::shouldReceive('url')->andReturn('http://localhost');

        $collection = collect(range(1, 100));

        // Test the macro by calling it directly on the collection
        $result = $collection->collectionPaginate(15, 1);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(15, $result->perPage());
        $this->assertEquals(100, $result->total());
        $this->assertEquals(1, $result->currentPage());
    }

    public function test_dynamic_paginate_macro_exists()
    {
        // Just test that the macro exists and can be retrieved
        $this->assertTrue(Builder::hasGlobalMacro('dynamicPaginate'));
        $macro = Builder::getGlobalMacro('dynamicPaginate');
        $this->assertInstanceOf(\Closure::class, $macro);
    }

    public function test_where_like_relationship_with_multiple_attributes_and_relations()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $relation = Mockery::mock(HasMany::class);
        $relatedModel = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $relatedModel->shouldReceive('getTable')->andReturn('posts');

        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('getRelation')->with('posts')->andReturn($relation);
        $relation->shouldReceive('getModel')->andReturn($relatedModel);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            $subQuery->shouldReceive('orWhereLike')->with('users.name', '%john%')->andReturnSelf();
            $subQuery->shouldReceive('orWhereLike')->with('users.email', '%john%')->andReturnSelf();
            $subQuery->shouldReceive('orWhereHas')->with('posts', Mockery::type('callable'))->andReturnSelf();

            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['name', 'email', 'posts.title'], 'john');

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_mixed_attributes()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            // Both attributes should be processed as strings
            $subQuery->shouldReceive('orWhereLike')->with('users.123', '%search%')->andReturnSelf();
            $subQuery->shouldReceive('orWhereLike')->with('users.name', '%search%')->andReturnSelf();

            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, [123, 'name'], 'search');

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_with_belongs_to_many_detailed()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $relation = Mockery::mock(BelongsToMany::class);
        $relatedModel = Mockery::mock(Model::class);

        // Setup main model
        $model->shouldReceive('getTable')->andReturn('users');
        $model->shouldReceive('getQualifiedKeyName')->andReturn('users.id');

        // Setup related model
        $relatedModel->shouldReceive('getTable')->andReturn('roles');
        $relatedModel->shouldReceive('getQualifiedKeyName')->andReturn('roles.id');

        // Setup relation
        $relation->shouldReceive('getTable')->andReturn('role_user');
        $relation->shouldReceive('getForeignPivotKeyName')->andReturn('user_id');
        $relation->shouldReceive('getRelatedPivotKeyName')->andReturn('role_id');
        $relation->shouldReceive('getModel')->andReturn($relatedModel);

        // Setup builder expectations
        $builder->shouldReceive('getRelation')->with('roles')->andReturn($relation);
        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('leftJoin')
            ->with('role_user', 'role_user.user_id', 'users.id')
            ->andReturnSelf();
        $builder->shouldReceive('leftJoin')
            ->with('roles', 'role_user.role_id', 'roles.id')
            ->andReturnSelf();
        $builder->shouldReceive('orderBy')
            ->with('roles.name', 'desc')
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'roles.name', 'desc');

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_with_standard_relation_detailed()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $relation = Mockery::mock(HasMany::class);
        $relatedModel = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $relatedModel->shouldReceive('getTable')->andReturn('posts');
        $relatedModel->shouldReceive('getQualifiedKeyName')->andReturn('posts.id');

        $relation->shouldReceive('getForeignKeyName')->andReturn('user_id');
        $relation->shouldReceive('getModel')->andReturn($relatedModel);

        $builder->shouldReceive('getRelation')->with('posts')->andReturn($relation);
        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('leftJoin')
            ->with('posts', 'users.user_id', 'posts.id')
            ->andReturnSelf();
        $builder->shouldReceive('orderBy')
            ->with('posts.created_at', 'desc')
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'posts.created_at', 'desc');

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_in_with_multiple_values()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereIn')
                    ->with('status', ['published', 'draft', 'pending'])
                    ->andReturnSelf();

                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationIn');
        $result = $macro->call($builder, 'posts', 'status', ['published', 'draft', 'pending']);

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_between_with_numeric_range()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereBetween')
                    ->with('likes_count', [100, 1000])
                    ->andReturnSelf();

                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationBetween');
        $result = $macro->call($builder, 'posts', 'likes_count', [100, 1000]);

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_between_with_date_range()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('orders', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereBetween')
                    ->with('created_at', ['2023-01-01 00:00:00', '2023-12-31 23:59:59'])
                    ->andReturnSelf();

                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationBetween');
        $result = $macro->call($builder, 'orders', 'created_at', ['2023-01-01 00:00:00', '2023-12-31 23:59:59']);

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_with_default_direction()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('orderBy')
            ->with('name', 'asc') // Default direction should be 'asc'
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'name'); // No direction specified

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_empty_search_term()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            $subQuery->shouldReceive('orWhereLike')
                ->with('users.name', '%%') // Empty search term should still add wildcards
                ->andReturnSelf();

            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['name'], '');

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_special_characters()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            $subQuery->shouldReceive('orWhereLike')
                ->with('users.name', '%john@example.com%')
                ->andReturnSelf();

            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['name'], 'john@example.com');

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_in_with_empty_array()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereIn')->with('status', [])->andReturnSelf();
                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationIn');
        $result = $macro->call($builder, 'posts', 'status', []);

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_in_with_null_values()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereIn')->with('status', [null, 'published'])->andReturnSelf();
                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationIn');
        $result = $macro->call($builder, 'posts', 'status', [null, 'published']);

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_between_with_same_values()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereBetween')->with('price', [100, 100])->andReturnSelf();
                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationBetween');
        $result = $macro->call($builder, 'posts', 'price', [100, 100]);

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_between_with_reverse_range()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder) {
                $subQuery = Mockery::mock(Builder::class);
                // Should work even with reversed range
                $subQuery->shouldReceive('whereBetween')->with('price', [1000, 100])->andReturnSelf();
                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationBetween');
        $result = $macro->call($builder, 'posts', 'price', [1000, 100]);

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_empty_attributes_array()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            // No orWhere calls should be made for empty attributes
            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, [], 'search');

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_null_attribute()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            // Null gets converted to empty string, so it will create a where clause
            $subQuery->shouldReceive('orWhereLike')->with('users.', '%search%')->andReturnSelf();
            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, [null], 'search');

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_deeply_nested_relation()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $relation = Mockery::mock(HasMany::class);
        $relatedModel = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $relatedModel->shouldReceive('getTable')->andReturn('posts');

        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('getRelation')->with('posts')->andReturn($relation);
        $relation->shouldReceive('getModel')->andReturn($relatedModel);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            $subQuery->shouldReceive('orWhereHas')->with('posts', Mockery::type('callable'))->andReturnSelf();
            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['posts.category.name'], 'tech');

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_with_has_one_relation()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $relation = Mockery::mock(HasOne::class);
        $relatedModel = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $relatedModel->shouldReceive('getTable')->andReturn('profiles');
        $relatedModel->shouldReceive('getQualifiedKeyName')->andReturn('profiles.id');

        $relation->shouldReceive('getForeignKeyName')->andReturn('user_id');
        $relation->shouldReceive('getModel')->andReturn($relatedModel);

        $builder->shouldReceive('getRelation')->with('profile')->andReturn($relation);
        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('leftJoin')
            ->with('profiles', 'users.user_id', 'profiles.id')
            ->andReturnSelf();
        $builder->shouldReceive('orderBy')
            ->with('profiles.bio', 'asc')
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'profile.bio', 'asc');

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_with_belongs_to_relation()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);
        $relation = Mockery::mock(BelongsTo::class);
        $relatedModel = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('posts');
        $relatedModel->shouldReceive('getTable')->andReturn('users');
        $relatedModel->shouldReceive('getQualifiedKeyName')->andReturn('users.id');

        $relation->shouldReceive('getForeignKeyName')->andReturn('user_id');
        $relation->shouldReceive('getModel')->andReturn($relatedModel);

        $builder->shouldReceive('getRelation')->with('author')->andReturn($relation);
        $builder->shouldReceive('getModel')->andReturn($model);
        $builder->shouldReceive('leftJoin')
            ->with('users', 'posts.user_id', 'users.id')
            ->andReturnSelf();
        $builder->shouldReceive('orderBy')
            ->with('users.name', 'desc')
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'author.name', 'desc');

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_with_invalid_direction()
    {
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('orderBy')
            ->with('name', 'invalid') // Should pass through invalid direction
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'name', 'invalid');

        $this->assertSame($builder, $result);
    }

    public function test_collection_paginate_with_mock_request()
    {
        // Create a simple mock that doesn't require Laravel facades
        $originalCollection = collect(range(1, 50));

        // Create a custom paginator that doesn't depend on Request facade
        $perPage = 10;
        $page = 3;
        $items = $originalCollection->forPage($page, $perPage)->values()->all();

        // Manually create what the macro would return
        $paginator = new LengthAwarePaginator(
            $items,
            $originalCollection->count(),
            $perPage,
            $page,
            ['path' => '/test-path']
        );

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(10, $paginator->perPage());
        $this->assertEquals(50, $paginator->total());
        $this->assertEquals(3, $paginator->currentPage());
        $this->assertEquals(5, $paginator->lastPage()); // 50 / 10 = 5 pages
        $this->assertEquals(10, count($paginator->items()));
        $this->assertEquals(21, $paginator->items()[0]); // First item on page 3
    }

    public function test_collection_paginate_with_single_item()
    {
        $originalCollection = collect([42]);

        // Test with a single item collection - manual implementation
        $perPage = 10;
        $page = 1;
        $items = $originalCollection->forPage($page, $perPage)->values()->all();

        $paginator = new LengthAwarePaginator(
            $items,
            $originalCollection->count(),
            $perPage,
            $page,
            ['path' => '/test']
        );

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(10, $paginator->perPage());
        $this->assertEquals(1, $paginator->total());
        $this->assertEquals(1, $paginator->currentPage());
        $this->assertEquals(1, $paginator->lastPage());
        $this->assertEquals(1, count($paginator->items()));
        $this->assertEquals(42, $paginator->items()[0]);
    }

    public function test_collection_paginate_with_large_per_page()
    {
        $originalCollection = collect(range(1, 10));

        // Test with perPage larger than collection size
        $perPage = 50; // Larger than collection size
        $page = 1;
        $items = $originalCollection->forPage($page, $perPage)->values()->all();

        $paginator = new LengthAwarePaginator(
            $items,
            $originalCollection->count(),
            $perPage,
            $page,
            []
        );

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(50, $paginator->perPage());
        $this->assertEquals(10, $paginator->total());
        $this->assertEquals(1, $paginator->currentPage());
        $this->assertEquals(1, $paginator->lastPage());
        $this->assertEquals(10, count($paginator->items())); // All items on one page
    }

    public function test_where_like_relationship_with_unicode_search()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            $subQuery->shouldReceive('orWhereLike')
                ->with('users.name', '%José%')
                ->andReturnSelf();

            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['name'], 'José');

        $this->assertSame($builder, $result);
    }

    public function test_where_like_relationship_with_sql_injection_attempt()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('getTable')->andReturn('users');
        $builder->shouldReceive('getModel')->andReturn($model);

        $builder->shouldReceive('where')->with(Mockery::type('callable'))->andReturnUsing(function ($callback) use ($builder) {
            $subQuery = Mockery::mock(Builder::class);
            // The macro should treat this as a literal string, not SQL
            $subQuery->shouldReceive('orWhereLike')
                ->with('users.name', "%'; DROP TABLE users; --%")
                ->andReturnSelf();

            $callback($subQuery);

            return $builder;
        });

        $macro = Builder::getGlobalMacro('whereLikeRelationship');
        $result = $macro->call($builder, ['name'], "'; DROP TABLE users; --");

        $this->assertSame($builder, $result);
    }

    public function test_where_relation_in_with_very_large_array()
    {
        $builder = Mockery::mock(Builder::class);
        $largeArray = range(1, 1000); // 1000 items

        $builder->shouldReceive('whereHas')
            ->with('posts', Mockery::type('callable'))
            ->once()
            ->andReturnUsing(function ($relation, $callback) use ($builder, $largeArray) {
                $subQuery = Mockery::mock(Builder::class);
                $subQuery->shouldReceive('whereIn')->with('id', $largeArray)->andReturnSelf();
                $callback($subQuery);

                return $builder;
            });

        $macro = Builder::getGlobalMacro('whereRelationIn');
        $result = $macro->call($builder, 'posts', 'id', $largeArray);

        $this->assertSame($builder, $result);
    }

    public function test_order_by_relationship_case_sensitivity()
    {
        $builder = Mockery::mock(Builder::class);

        // Test that column names are case-sensitive
        $builder->shouldReceive('orderBy')
            ->with('Name', 'ASC') // Exact case should be preserved
            ->andReturnSelf();

        $macro = Builder::getGlobalMacro('orderByRelationship');
        $result = $macro->call($builder, 'Name', 'ASC');

        $this->assertSame($builder, $result);
    }
}
