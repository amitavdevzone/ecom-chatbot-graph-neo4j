## Context

This is a server-to-server API surface. An external shop system calls into this Laravel application using a shared static secret. No user session or Sanctum token flow is involved — authentication is purely header-based on every request.

The existing data model already supports the required query: `Customer` → `orders` → `orderItems` → `product`. No schema changes are needed.

## Goals / Non-Goals

**Goals:**
- Authenticate server-to-server requests via a reusable middleware
- Expose customer order history through a single protected endpoint
- Shape responses through typed API Resources (DTO layer)

**Non-Goals:**
- Per-user or per-device token management (Sanctum, OAuth)
- Token rotation or expiry — the shared secret is managed via `.env`
- Pagination of orders — latest 5 is the fixed contract for now
- Exposing order item counts, totals, or aggregates beyond what is in the model

## Decisions

### 1. Static token comparison in middleware, not a guard

**Decision**: Implement `ValidateShopToken` as a standalone middleware that compares the header value to `config('services.laravel-shop-token.access_token')` using a constant-time comparison (`hash_equals`).

**Why not a custom Auth guard**: Guards are designed for user identity resolution. This token does not represent a user — it represents a trusted caller. Middleware is the right primitive for a binary allow/deny gate with no identity attached.

**Why `hash_equals`**: Prevents timing attacks when comparing secret strings, which matters even for internal APIs.

### 2. API Resources as the response DTO layer

**Decision**: Use `OrderResource` and `OrderItemResource` (Eloquent API Resources) to shape the JSON response.

**Why not plain PHP readonly classes**: API Resources are the Laravel-idiomatic DTO for JSON output. They handle collection wrapping, lazy-loading guards, and integrate with the response pipeline. The project guidelines (`CLAUDE.md`) call for API Resources on API endpoints.

### 3. 404 for unknown email

**Decision**: Return `404 Not Found` when the posted email does not match any `Customer`.

**Why not 200 with empty orders**: This is a trusted internal API behind a shared secret. Leaking whether an email is a known customer is not a concern here. A 404 is a cleaner signal for the caller to handle.

### 4. Eager load the full chain

**Decision**: Load `orderItems.product` via eager loading in the controller query.

```
Customer::where('email', $email)
    ->with(['orders' => fn($q) => $q->latest()->limit(5)])
    ->firstOrFail()
```

Then within `OrderResource`, eager-load `orderItems.product` on the orders collection to avoid N+1.

**Alternative**: Lazy loading — rejected because it produces N+1 queries for items and products.

## Risks / Trade-offs

- **Shared secret in `.env`**: If the secret leaks, all endpoints using `ValidateShopToken` are exposed. Mitigation: secret should be rotated via `.env` change and redeployment; no code change needed.
- **`limit(5)` is hardcoded**: The response contract is fixed to 5 orders. If the caller later needs more, this requires an endpoint change. Mitigation: acceptable for now; document the limit in the spec.
- **No rate limiting**: The middleware does not rate-limit requests. Acceptable for trusted server-to-server use; revisit if the endpoint becomes externally exposed.
