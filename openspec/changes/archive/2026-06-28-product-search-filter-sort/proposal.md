## Why

The agentic app now uses an LLM query-builder node to convert natural language into structured Typesense queries (`query`, `filter_by`, `sort_by`), but the Laravel `/api/products/search` endpoint only accepts `query` and ignores the filter and sort parameters. Without backend support, structured intent from the agent is silently discarded, producing the same unfiltered results as raw keyword search.

## What Changes

- Update `/api/products/search` to accept optional `filter_by` and `sort_by` request fields
- Pass `filter_by` and `sort_by` through to the Typesense search call via Laravel Scout
- Remove the hardcoded `"mobile"` query override that was left as a temporary placeholder
- Update validation rules to allow the new optional fields
- Update the `ProductResource` or response shape if needed to reflect filtered/sorted results

## Capabilities

### New Capabilities

*(none — this change extends an existing capability)*

### Modified Capabilities

- `product-search-api`: Endpoint now accepts optional `filter_by` and `sort_by` fields alongside `query`, passes them to Typesense, and removes the temporary hardcoded query override

## Impact

- **Controller**: `app/Http/Controllers/ProductSearchController.php` — read and forward new fields
- **Request validation**: accept optional `filter_by` (string) and `sort_by` (string) fields
- **Scout/Typesense call**: apply `filter_by` and `sort_by` to `Product::search()` builder
- **Spec delta**: `openspec/changes/product-search-filter-sort/specs/product-search-api/spec.md`
- No new routes or models; no breaking changes to existing `query`-only callers (new fields are optional)
