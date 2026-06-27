## Why

The Jarvis agentic Node.js app needs to search products by free-text query as a new `product` intent. Laravel is the data source — it must expose a search endpoint so Jarvis can retrieve up to 10 candidate products for LLM-based re-ranking on the Node side.

## What Changes

- New `POST /api/products/search` endpoint accepting a `query` string
- ILIKE search across product `name` and `description` columns (max 10 results)
- New `ProductResource` returning `id`, `name`, `price`, and `description`
- New `ProductSearchController` wired to the existing `shop.token` middleware
- Pest feature test covering the search behaviour and auth

## Capabilities

### New Capabilities

- `product-search-api`: Free-text product search endpoint returning up to 10 matching products by name and description

### Modified Capabilities

## Impact

- `routes/api.php` — new route added
- `app/Http/Controllers/ProductSearchController.php` — new file
- `app/Http/Resources/ProductResource.php` — new file
- No schema changes, no new dependencies
- Typesense integration deferred; search quality relies on ILIKE for now
