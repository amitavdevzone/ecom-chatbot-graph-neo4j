## 1. Middleware

- [x] 1.1 Create `ValidateShopToken` middleware using `php artisan make:middleware`
- [x] 1.2 Implement header check: compare `X-Laravel-Auth-Token` against `config('services.laravel-shop-token.access_token')` using `hash_equals`; return 401 JSON on failure
- [x] 1.3 Register the middleware as a named alias in `bootstrap/app.php`

## 2. API Resources

- [x] 2.1 Create `OrderItemResource` — fields: `quantity`, `price`, nested `product` with `name` and `category`
- [x] 2.2 Create `OrderResource` — fields: `id`, `total_amount`, `created_at`, and `items` as `OrderItemResource::collection($this->orderItems)`

## 3. Controller

- [x] 3.1 Create `OrderStatusController` using `php artisan make:controller`
- [x] 3.2 Implement `__invoke` method: validate `email` field, look up `Customer` with eager-loaded `orders.orderItems.product` (latest 5), return `OrderResource::collection`

## 4. Routing

- [x] 4.1 Register `POST /api/order-status` in `routes/api.php` with the `ValidateShopToken` middleware applied

## 5. Tests

- [ ] 5.1 Write feature test for `ValidateShopToken`: missing header → 401, wrong token → 401, correct token → passes through
- [ ] 5.2 Write feature test for `POST /api/order-status`: valid email returns 200 with correct order + item + product structure
- [ ] 5.3 Write feature test: unknown email returns 404
- [ ] 5.4 Write feature test: missing email returns 422
- [ ] 5.5 Run `php artisan test --compact` and confirm all tests pass
- [ ] 5.6 Run `vendor/bin/pint --dirty --format agent` to fix any style issues
