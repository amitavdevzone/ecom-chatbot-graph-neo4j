## 1. Database & Model Prep

- [x] 1.1 Create migration to add `updated_at` column (nullable) to the `products` table
- [x] 1.2 Remove `public const UPDATED_AT = null` from `App\Models\Product`
- [x] 1.3 Run migration and verify column exists

## 2. Install & Configure Scout

- [x] 2.1 Run `composer require laravel/scout typesense/typesense-php`
- [x] 2.2 Publish Scout config: `php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"`
- [x] 2.3 Configure `config/scout.php` with Typesense driver settings (host, port, api_key, collection schema source)
- [x] 2.4 Add `SCOUT_DRIVER=typesense`, `TYPESENSE_API_KEY`, `TYPESENSE_HOST`, `TYPESENSE_PORT=8108` to `.env` and `.env.example`

## 3. Product Model — Searchable Integration

- [x] 3.1 Add `use Laravel\Scout\Searchable;` trait to `App\Models\Product`
- [x] 3.2 Implement `toSearchableArray()` — cast `id` to string, `category` to enum value, `price` to float, timestamps to int32 unix, `description`/`updated_at` nullable
- [x] 3.3 Implement `typesenseCollectionSchema()` — define all fields with correct types, `description` optional, `category` facetable

## 4. Bulk Sync Command

- [x] 4.1 Create `App\Console\Commands\SyncToTypesense` via `php artisan make:command SyncToTypesense`
- [x] 4.2 Set signature to `typesense:sync-products` and add description
- [x] 4.3 Implement `handle()` to output info message, call `scout:import "App\Models\Product"`, then output success message
- [x] 4.4 Run `php artisan typesense:sync-products` and verify products appear in Typesense dashboard (port 8109)

## 5. Product Search Controller

- [x] 5.1 Replace `Product::query()->whereLike(...)` in `ProductSearchController` with `Product::search($query)`
- [x] 5.2 Keep the hardcoded `$query = "mobile"` override in place

## 6. Tests

- [x] 6.1 Write feature test for `typesense:sync-products` command (mock Scout, assert `scout:import` is called)
- [x] 6.2 Write feature test for `ProductSearchController` asserting `Product::search()` is used
- [x] 6.3 Run `php artisan test --compact` and confirm all tests pass

## 7. Code Style

- [x] 7.1 Run `vendor/bin/pint --dirty --format agent` on all modified PHP files
