## Context

Laravel is the data backend for the Jarvis agentic chatbot. Jarvis already calls `/api/order-status` to fetch order data; it now needs a product search endpoint to back the new `product` intent. The Node side handles LLM-based re-ranking, so Laravel's only job is retrieval.

The existing API pattern: an invokable controller, a Form Request for validation, an Eloquent API Resource for output shape, and the `shop.token` middleware for auth. No new packages are needed.

## Goals / Non-Goals

**Goals:**
- Expose `POST /api/products/search` accepting a free-text `query`
- Search `name` and `description` columns with case-insensitive ILIKE
- Return up to 10 results shaped as `{id, name, price, description}`
- Protect the endpoint with the existing `ValidateShopToken` middleware

**Non-Goals:**
- Typesense / Laravel Scout integration (deferred)
- Category filtering or structured query parameters
- Sorting (done Node-side by the LLM ranker)
- Pagination

## Decisions

### D1 — ILIKE over `name` and `description` only

**Decision**: Search is `WHERE name ILIKE '%{query}%' OR description ILIKE '%{query}%'`. Category is excluded from search matching and from the response.

**Rationale**: The Node team explicitly asked for name+description only. Category is an enum and would need exact-match logic, not free-text. Keeping it out of the response avoids leaking the enum structure to the consumer.

**Alternative considered**: Full-text search using `to_tsvector`. Rejected for now — adds complexity and the product catalog is small. Can be added later without changing the API contract.

### D2 — Follow existing invokable controller pattern

**Decision**: `ProductSearchController` is an invokable class, mirroring `OrderStatusController`.

**Rationale**: Consistency with the only other API controller in the project. Single-action controllers keep route definitions terse.

### D3 — `ProductResource` as a new standalone resource

**Decision**: Create `ProductResource` returning `id`, `name`, `price`, `description`. No reference to `OrderItemResource` which already has a local product inline shape.

**Rationale**: `OrderItemResource` embeds a minimal product shape (`name`, `category`) for a different consumer. A dedicated `ProductResource` gives the search endpoint its own clean contract and allows the two shapes to evolve independently.

## Risks / Trade-offs

- **ILIKE performance on large tables** → acceptable for a tutorial-scale catalog; add a GIN index on `name`+`description` if the table grows significantly
- **No relevance scoring** → results are unordered; the Node LLM ranker compensates. Results could be noisy for short queries like "phone"
- **Query injection** → mitigated by using Eloquent's parameterised binding, never raw string interpolation

## Migration Plan

Additive change only. No schema changes, no data migration. Deploy, done.
