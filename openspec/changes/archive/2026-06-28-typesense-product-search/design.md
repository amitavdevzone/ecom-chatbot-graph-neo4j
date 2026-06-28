## Context

Product search currently runs `LIKE` queries directly on PostgreSQL via `ProductSearchController`. Typesense is already running in the stack (port 8108, docker-compose). Laravel Scout v10+ ships with a built-in Typesense driver — no third-party Scout adapter needed.

The `Product` model has `updated_at` disabled (`UPDATED_AT = null`) for an undocumented reason. This needs to be restored since Scout and general auditing benefit from it.

## Goals / Non-Goals

**Goals:**
- Products indexed in Typesense with a defined, stable schema
- Scout model observer handles create/update/delete lifecycle automatically
- Idempotent `typesense:sync-products` command for bulk re-indexing
- `ProductSearchController` delegates to Typesense via `Product::search()`

**Non-Goals:**
- Faceted search UI or filter widgets
- Advanced ranking tuning or boosting rules
- Search analytics or query logging
- Indexing relationships (orderItems, productLikes)

## Decisions

### 1. Scout built-in Typesense driver over custom SDK usage

Laravel Scout v10+ includes Typesense as a first-class driver. Using it means lifecycle sync (create/update/delete) is wired automatically via the `Searchable` trait — no custom observer needed. The alternative (direct `typesense/typesense-php` SDK calls) gives more control but duplicates what Scout already provides for no benefit at this scale.

### 2. Collection schema defined on the model via `typesenseCollectionSchema()`

Scout's Typesense integration reads the schema from a static method on the model. This keeps schema definition co-located with the model rather than scattered across a config file or service class. It is the idiomatic Scout + Typesense pattern.

Schema:
```
id            string   (Typesense requires string IDs)
name          string   searchable
description   string   searchable, optional (nullable in DB)
category      string   facet: true (enum value, lowercase)
price         float    sortable / filterable
created_at    int32    unix timestamp, sortable
updated_at    int32    unix timestamp, optional (nullable on existing rows)
```

### 3. Custom command wraps `scout:import` rather than re-implementing

`php artisan scout:import` already handles collection creation and upsert. The custom `typesense:sync-products` command wraps it with descriptive output, following the pattern of the existing `SyncToNeo4j` command. This avoids duplicating Scout internals.

### 4. `description` marked optional in Typesense schema

The `description` column is nullable in PostgreSQL. Rather than coercing `null` to an empty string in `toSearchableArray()`, the Typesense field is marked `'optional' => true`. This means absent descriptions are simply not indexed rather than being searchable empty strings.

## Risks / Trade-offs

**Schema changes require collection drop and recreate**
→ Typesense does not support altering field types on existing collections. If the schema needs to change in future, the collection must be dropped and `typesense:sync-products` re-run. Mitigated by locking the schema carefully now.

**Bulk DB updates bypass Scout lifecycle events**
→ `Product::where(...)->update([...])` does not fire model events, so Typesense won't auto-sync. The `typesense:sync-products` command is the recovery path — it should be run after any bulk DB operation. This is documented behaviour, not a bug.

**`updated_at` is `null` for all existing rows after migration**
→ The migration adds the column as nullable with no default. Existing products will have `null` for `updated_at`. The Typesense field is marked optional so this doesn't block indexing. After the first save via Filament or the sync command, rows will get a timestamp.

## Migration Plan

1. Run `php artisan migrate` to add `updated_at` to `products`
2. `composer require laravel/scout typesense/typesense-php`
3. `php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"`
4. Configure `config/scout.php` and `.env` with Typesense credentials
5. Add `Searchable` trait + schema methods to `Product` model
6. Add `SyncToTypesense` command
7. Update `ProductSearchController` to use `Product::search()`
8. Run `php artisan typesense:sync-products` to populate the collection
9. Verify via Typesense dashboard (port 8109)
