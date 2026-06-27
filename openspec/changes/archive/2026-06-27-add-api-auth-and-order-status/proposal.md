## Why

The application needs to expose an API endpoint for a trusted shop integration to query customer order history. Authentication is server-to-server using a shared static token — callers always present the token directly, no login step is required.

## What Changes

- Add `ValidateShopToken` middleware that authenticates requests by comparing the `X-Laravel-Auth-Token` header against the configured `services.laravel-shop-token.access_token` value; returns 401 if missing or mismatched
- Add `POST /api/order-status` endpoint that accepts a customer email and returns their latest 5 orders with nested items and product details; returns 404 if the email does not match any customer
- Add `OrderResource` and `OrderItemResource` API Resources as the response DTO layer

## Capabilities

### New Capabilities

- `shop-token-auth`: Reusable middleware for validating the static `X-Laravel-Auth-Token` header against the configured shop secret — applicable to any future server-to-server endpoints
- `order-status`: Protected endpoint returning the latest 5 orders (with items and product info) for a customer looked up by email

### Modified Capabilities

<!-- None — no existing capability requirements are changing -->

## Impact

- `app/Http/Middleware/ValidateShopToken.php` — new middleware
- `app/Http/Controllers/OrderStatusController.php` — new controller
- `app/Http/Resources/OrderResource.php` — new API Resource
- `app/Http/Resources/OrderItemResource.php` — new API Resource
- `routes/api.php` — one new POST route
- No model changes, no migrations, no breaking changes to existing routes
