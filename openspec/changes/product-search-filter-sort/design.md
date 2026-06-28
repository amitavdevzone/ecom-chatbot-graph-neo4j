## Context

The Laravel `/api/products/search` endpoint is called by the agentic app's `searchProducts` tool. Currently it only accepts a `query` string and applies a hardcoded `"mobile"` override (a leftover from early development). The agent now constructs structured Typesense queries via an LLM query-builder node and sends `filter_by` and `sort_by` alongside `query`. These parameters are silently ignored by the current backend.

Scout's Typesense driver exposes an `options()` method on the search builder that accepts raw Typesense search parameters, making passthrough straightforward without bypassing Scout's abstraction.

## Goals / Non-Goals

**Goals:**
- Accept optional `filter_by` and `sort_by` string fields in the request body
- Forward them to Typesense via the Scout builder's `options()` call
- Remove the hardcoded `"mobile"` query override

**Non-Goals:**
- Validating or interpreting the Typesense filter/sort syntax server-side (that is the agent's responsibility)
- Changes to the Typesense schema or indexed fields
- Changes to the `ProductResource` response shape
- Authentication or rate-limiting changes

## Decisions

### Pass filter/sort as raw Scout options

**Decision**: Use `Product::search($query)->options([...])->take(10)->get()` to forward Typesense params.

**Rationale**: Scout's `options()` method is designed exactly for passing driver-specific parameters without breaking the Scout abstraction. Alternatives like constructing a raw Typesense client call would bypass Scout, introduce a direct dependency, and complicate future driver swaps.

**Filter/Sort are optional**: Both fields are optional strings. When absent the search runs as a plain keyword query, preserving backward compatibility for any caller that sends only `query`.

### Remove the hardcoded query override

**Decision**: Delete the `$query = 'mobile';` line in the controller.

**Rationale**: It was a temporary debugging aid. The Typesense index is confirmed populated and the search pipeline is validated; keeping the override prevents any meaningful search test from running.

## Risks / Trade-offs

- **Malformed Typesense syntax** → Typesense returns a 400; the controller will propagate this as a 500 unless we wrap and re-surface the error. For now we accept this: the agent controls the query builder and should emit valid syntax. A validation wrapper can be added later if needed.
- **No server-side sanitization of filter values** → The endpoint is protected by token auth so only trusted agent callers reach it; raw passthrough is acceptable.

## Migration Plan

1. Update `ProductSearchController` to validate and read `filter_by` / `sort_by`
2. Build `options` array conditionally (only include fields when present)
3. Remove the hardcoded `$query = 'mobile'` override
4. Update/add Pest feature tests to cover the new fields and verify the override is gone
5. No migration needed — no DB schema changes
