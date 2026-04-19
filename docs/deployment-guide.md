# Deployment & Installation Guide

## Prerequisites

- **PHP**: 8.3 or higher
- **Laravel**: 11.0, 12.0, or 13.0
- **Composer**: 2.0 or higher
- **Database**: MySQL, PostgreSQL, SQLite, or other Eloquent-supported database

## Installation

### Step 1: Add Package via Composer

```bash
composer require larajs/query:^2.0
```

**Verify installation**:
```bash
composer show larajs/query
```

Expected output:
```
name     : larajs/query
descrip. : Dynamic API Query Builder for Laravel
keywords : laravel, eloquent, query-builder, filtering, sorting
versions : * v2.0.0
type     : library
license  : MIT (or your license)
homepage : https://github.com/larajs/query
source   : [git] https://github.com/larajs/query.git
dist     : [zip] ...
requires : php ^8.3, illuminate/support ^11|^12|^13, illuminate/database ^11|^12|^13, illuminate/http ^11|^12|^13, illuminate/pagination ^11|^12|^13, staudenmeir/belongs-to-through ^2.17
```

### Step 2: Publish Configuration (Optional)

Publish the config file to customize defaults:

```bash
php artisan vendor:publish --tag=larajs-query
```

This creates `config/larajs-query.php`:

```php
return [
    'limit' => [
        'default' => 25,   // Default page size
        'max'     => 500,  // Max allowed limit
    ],
];
```

**Customize if needed** for your application's performance requirements.

### Step 3: Verify Setup

Create a test controller to verify installation:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use LaraJS\Query\LaraJSQuery;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;

class UserController extends Controller
{
    use LaraJSQuery;

    public function index(Request $request)
    {
        // Define allowed query parameters
        $allow = QueryParserAllowDTO::fromArray([
            'field'   => ['id', 'name', 'email', 'created_at'],
            'filter'  => ['email', 'name'],
            'sort'    => ['id', 'name', 'created_at'],
            'include' => ['posts', 'roles'],
            'search'  => ['name', 'email'],
        ]);

        // Apply LaraJS Query
        $users = User::query()
            ->applyLaraJSQuery(
                QueryParserRequestDTO::fromArray($request->query()),
                $allow
            )
            ->get();

        return response()->json($users);
    }
}
```

Test the endpoint:

```bash
curl "http://localhost:8000/api/users?filter=equals(name,'John')"
curl "http://localhost:8000/api/users?sort=name&pagination[limit]=10&pagination[page]=1"
curl "http://localhost:8000/api/users?include[]=posts&include[]=roles"
```

## Usage Patterns

### 1. Controller with LaraJSQuery Trait

**Simplest approach** — use trait directly in controller:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use LaraJS\Query\LaraJSQuery;
use LaraJS\Query\DTO\QueryParserAllowDTO;
use LaraJS\Query\DTO\QueryParserRequestDTO;

class ProductController extends Controller
{
    use LaraJSQuery;

    public function index(Request $request)
    {
        $allow = QueryParserAllowDTO::fromArray([
            'filter' => ['name', 'price', 'category_id'],
            'sort'   => ['name', 'price', 'created_at'],
            'include' => ['category', 'reviews'],
        ]);

        return Product::query()
            ->applyLaraJSQuery(
                QueryParserRequestDTO::fromArray($request->query()),
                $allow
            )
            ->paginate();
    }
}
```

### 2. Repository Pattern (Recommended)

**Decouples business logic** from HTTP concerns:

```php
<?php

namespace App\Repositories;

use App\Models\Product;
use LaraJS\Query\Repositories\ReadRepository;
use LaraJS\Query\DTO\QueryParserAllowDTO;

class ProductRepository extends ReadRepository
{
    public function __construct()
    {
        parent::__construct(
            model: new Product(),
            limit: 25,
            maxLimit: 500,
        );
    }

    // Custom method combining LaraJSQuery with business logic
    public function findActiveProducts(QueryParserAllowDTO $allow)
    {
        return $this->applyLaraJSQuery(
            $this->model::query()->where('is_active', true),
            QueryParserRequestDTO::fromArray(request()->query()),
            $allow
        )->paginate($this->limit);
    }

    // Full repository interface
    public function getAllowedFields(): QueryParserAllowDTO
    {
        return QueryParserAllowDTO::fromArray([
            'field'   => ['id', 'name', 'price', 'category_id'],
            'filter'  => ['name', 'price', 'is_active'],
            'sort'    => ['name', 'price', 'created_at'],
            'include' => ['category', 'reviews'],
            'search'  => ['name', 'description'],
        ]);
    }
}
```

**Controller using repository**:

```php
<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductRepository $products) {}

    public function index(Request $request)
    {
        return $this->products->findAll(
            $this->products->getAllowedFields()
        );
    }

    public function active(Request $request)
    {
        return $this->products->findActiveProducts(
            $this->products->getAllowedFields()
        );
    }
}
```

### 3. API Resource with Collections

**Formatting JSON responses**:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

**Controller**:

```php
public function index(Request $request)
{
    $products = Product::query()
        ->applyLaraJSQuery(...)
        ->get();

    return ProductResource::collection($products);
}
```

## Configuration

### File: `config/larajs-query.php`

Published after `vendor:publish --tag=larajs-query`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure default and maximum pagination limits to control data volume
    | and prevent resource exhaustion.
    |
    */
    'limit' => [
        'default' => 25,   // Default items per page
        'max'     => 500,  // Maximum allowed items per page
    ],
];
```

### Customization Examples

**High-traffic API (reduce defaults)**:
```php
'limit' => [
    'default' => 10,
    'max'     => 100,
]
```

**Analytics/reporting endpoint (allow large datasets)**:
```php
'limit' => [
    'default' => 100,
    'max'     => 5000,
]
```

## Model Setup

### Define Allowed Query Fields

Models don't require configuration, but you can add helper methods:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaraJS\Query\DTO\QueryParserAllowDTO;

class User extends Model
{
    // ... model definition

    /**
     * Define publicly queryable fields
     */
    public static function getQueryAllowList(): QueryParserAllowDTO
    {
        return QueryParserAllowDTO::fromArray([
            'field'   => ['id', 'name', 'email', 'created_at'],
            'filter'  => ['name', 'email', 'created_at'],
            'sort'    => ['id', 'name', 'created_at'],
            'include' => ['posts', 'roles'],
            'search'  => ['name', 'email'],
            'date'    => ['created_at'],
        ]);
    }
}
```

**Usage in controller**:

```php
public function index(Request $request)
{
    return User::query()
        ->applyLaraJSQuery(
            QueryParserRequestDTO::fromArray($request->query()),
            User::getQueryAllowList()
        )
        ->get();
}
```

## Routes Configuration

### Example Routes

```php
<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // User management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Product catalog
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
});
```

## Authorization & Security

### Implement Authorization in Repositories

```php
<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use LaraJS\Query\Repositories\ReadRepository;
use LaraJS\Query\DTO\QueryParserAllowDTO;

class PostRepository extends ReadRepository
{
    public function __construct()
    {
        parent::__construct(new Post(), 25, 500);
    }

    public function findAll(QueryParserAllowDTO $allow)
    {
        $user = Auth::user();

        // Base query: non-deleted posts
        $query = $this->model::where('deleted_at', null);

        // If user is admin, show all posts; otherwise show only published
        if (!$user?->isAdmin()) {
            $query = $query->where('published_at', '<=', now());
        }

        return $this->applyLaraJSQuery($query, ...$allow)->paginate();
    }
}
```

### Whitelist Sensitive Fields

```php
// Do NOT expose in allow-list:
'filter' => ['user_id', 'api_key', 'password_hash'], // BAD!

// Instead:
'filter' => ['status', 'category', 'name'], // OK
'filter' => ['public_field', 'created_at'],  // OK
```

## Testing

### Unit Test Example

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_users_by_name()
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->getJson('/api/users?filter=equals(name,"John Doe")');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Doe');
    }

    public function test_sort_users_by_name()
    {
        User::factory()->create(['name' => 'Zoe']);
        User::factory()->create(['name' => 'Alice']);

        $response = $this->getJson('/api/users?sort=name');

        $response->assertStatus(200);
        $this->assertEquals('Alice', $response->json('data.0.name'));
        $this->assertEquals('Zoe', $response->json('data.1.name'));
    }

    public function test_prevent_access_to_private_fields()
    {
        User::factory()->create(['password' => 'secret']);

        // Attempt to filter by password (not in allow-list)
        $response = $this->getJson('/api/users?filter=equals(password,"secret")');

        // Should not expose password data
        $response->assertStatus(200);
        $this->assertFalse(array_key_exists('password', $response->json('data.0', [])));
    }
}
```

## Troubleshooting

### Common Issues

#### 1. "Class LaraJSQuery not found"

**Cause**: Package not installed or autoloader not updated.

**Solution**:
```bash
composer require larajs/query:^2.0
composer dump-autoload
```

#### 2. "Filter syntax error" with complex expressions

**Cause**: Malformed filter expression.

**Solution**: Validate syntax:
```
Bad:  ?filter=equals(name'Smith')        // Missing comma
Good: ?filter=equals(name,'Smith')

Bad:  ?filter=and(equals(name,'Smith')   // Missing closing parenthesis
Good: ?filter=and(equals(name,'Smith'))

Bad:  ?filter=equals(name,Smith)         // Unquoted string
Good: ?filter=equals(name,'Smith')
```

#### 3. "Invalid operator" or "Unknown relation"

**Cause**: Field or relation not in allow-list.

**Solution**: Check QueryParserAllowDTO configuration:
```php
$allow = QueryParserAllowDTO::fromArray([
    'filter' => ['name', 'email'],  // Make sure field is here
    'include' => ['posts'],          // Make sure relation is here
]);
```

#### 4. N+1 Query Problem

**Cause**: Forgetting to include relationships.

**Solution**: Always include related models:
```php
// Bad: N+1 queries (1 user query + N post queries)
$users = User::all();
foreach ($users as $user) {
    echo $user->posts;
}

// Good: 2 queries (1 user + 1 posts)
$users = User::with('posts')->get();

// Good: Via LaraJSQuery
User::query()
    ->applyLaraJSQuery(
        QueryParserRequestDTO::fromArray($request->query()),
        QueryParserAllowDTO::fromArray(['include' => ['posts']])
    )
    ->get();
```

#### 5. Pagination Not Working

**Cause**: Forgot to call paginate() or get().

**Solution**: Choose execution method:
```php
// Returns Paginator
->paginate($limit)

// Returns simple Paginator
->simplePaginate($limit)

// Returns cursor Paginator
->cursorPaginate($limit)

// Returns Collection (no pagination)
->get()

// Returns single model
->first()
```

## Performance Optimization

### 1. Database Indexing

Create indexes on frequently sorted or filtered columns:

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->index('email');      // For filtering
    $table->index('name');       // For sorting
    $table->index('created_at'); // For date filtering
});
```

### 2. Eager Loading Strategy

```php
// Load relationships that will be included in response
$users = User::query()
    ->with('posts')        // Always load if included
    ->with('roles')
    ->applyLaraJSQuery(...)
    ->paginate();
```

### 3. Limit Query Complexity

```php
// Restrict max page size
'limit' => [
    'default' => 25,
    'max'     => 100,  // Prevent huge requests
]

// Restrict sortable columns (avoid unindexed sorts)
'sort' => ['name', 'created_at'],  // Only indexed columns
```

### 4. Response Formatting

```php
// Use API resources to control what's returned
return ProductResource::collection($products);

// Avoid lazy loading in resources
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'category' => new CategoryResource($this->whenLoaded('category')),
        // Use whenLoaded() to prevent extra queries
    ];
}
```

## Deployment Checklist

- [ ] Laravel 11/12/13 installed and running
- [ ] PHP 8.3+ available
- [ ] `composer require larajs/query:^2.0` successful
- [ ] Config file published (optional): `php artisan vendor:publish --tag=larajs-query`
- [ ] At least one controller/repository implemented with LaraJSQuery
- [ ] Allow-lists configured for all public endpoints
- [ ] Routes defined for API endpoints
- [ ] Authentication middleware applied to sensitive routes
- [ ] Tests written and passing
- [ ] Database indexes created for sorted/filtered columns
- [ ] Documentation updated for API consumers
- [ ] Rate limiting configured (recommended for public APIs)
- [ ] Error handling configured (middleware for exceptions)
- [ ] Monitoring/logging configured for query performance
- [ ] Tested with expected query parameter complexity
- [ ] Load tested for concurrent request volume

## Monitoring

### Log Slow Queries

```php
// In middleware or service provider
use Illuminate\Support\Facades\DB;

DB::listen(function ($query) {
    if ($query->time > 1000) {  // > 1 second
        \Log::warning('Slow query', [
            'time' => $query->time,
            'sql' => $query->sql,
            'bindings' => $query->bindings,
        ]);
    }
});
```

### Track LaraJSQuery Usage

```php
// In repository
public function findAll(QueryParserAllowDTO $allow)
{
    $start = microtime(true);

    $result = $this->applyLaraJSQuery(...)->paginate();

    \Log::info('LaraJSQuery executed', [
        'duration' => (microtime(true) - $start) * 1000,  // ms
        'model' => class_basename($this->model),
        'count' => $result->total(),
    ]);

    return $result;
}
```

## Support & Resources

- **Documentation**: `/docs/` directory
- **README**: `README.md`
- **Issues**: GitHub Issues
- **Email**: maingocthanhtan96@gmail.com

---

**Version**: 2.0.0
**Last Updated**: April 18, 2025
