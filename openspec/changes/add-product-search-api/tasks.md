## 1. API Resource

- [x] 1.1 Create `app/Http/Resources/ProductResource.php` returning `id`, `name`, `price`, `description`

## 2. Controller

- [x] 2.1 Create `app/Http/Controllers/ProductSearchController.php` as an invokable controller
- [x] 2.2 Validate `query` as required string via `$request->validate()`
- [x] 2.3 Query `Product` with `ILIKE` on `name` and `description`, limit 10
- [x] 2.4 Return `ProductResource::collection($products)`

## 3. Route

- [x] 3.1 Register `POST /api/products/search` in `routes/api.php` with `middleware('shop.token')`

## 4. Tests

- [ ] 4.1 Create `tests/Feature/ProductSearchTest.php` with `php artisan make:test --pest`
- [ ] 4.2 Test: authenticated request with matching query returns 200 and correct product fields
- [ ] 4.3 Test: query matching description only returns that product
- [ ] 4.4 Test: no matching products returns 200 with empty `data` array
- [ ] 4.5 Test: results capped at 10 when more than 10 products match
- [ ] 4.6 Test: missing `query` field returns 422
- [ ] 4.7 Test: missing auth token returns 401
- [ ] 4.8 Test: invalid auth token returns 401
- [ ] 4.9 Run `php artisan test --compact --filter=ProductSearch` and confirm all pass

## 5. Code Style

- [ ] 5.1 Run `vendor/bin/pint --dirty --format agent` on modified files
