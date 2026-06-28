## Why

Product search currently runs raw `LIKE` queries on the PostgreSQL database, which does not scale and lacks relevance ranking, faceting, or typo tolerance. Typesense is already running in the stack — wiring it up via Laravel Scout unlocks proper full-text search with minimal effort.

## What Changes

- Add `updated_at` column to the `products` table (was previously disabled)
- Install `laravel/scout` and `typesense/typesense-php`
- Add `Searchable` trait to the `Product` model with a defined collection schema and `toSearchableArray()`
- Add `typesense:sync-products` Artisan command for full bulk upsert
- Replace the raw DB query in `ProductSearchController` with `Product::search()`

## Capabilities

### New Capabilities

- `typesense-product-indexing`: Defines what the Typesense `products` collection looks like, how Product documents are shaped, and how the model stays in sync via Scout lifecycle events
- `typesense-bulk-sync`: Artisan command that creates the collection if missing and upserts all products — idempotent, safe to re-run after bulk DB changes

### Modified Capabilities

- `product-search`: The search endpoint no longer queries PostgreSQL directly — it now delegates to Typesense via `Product::search()`

## Impact

- **Models**: `Product` — adds `Searchable` trait, removes `UPDATED_AT = null`
- **Database**: New migration adding `updated_at` to `products`
- **Controllers**: `ProductSearchController` query replaced
- **Dependencies**: `laravel/scout`, `typesense/typesense-php` added
- **Config**: `config/scout.php` published and configured
- **Environment**: `SCOUT_DRIVER`, `TYPESENSE_API_KEY`, `TYPESENSE_HOST`, `TYPESENSE_PORT` added to `.env`
- **Commands**: New `app/Console/Commands/SyncToTypesense.php`
