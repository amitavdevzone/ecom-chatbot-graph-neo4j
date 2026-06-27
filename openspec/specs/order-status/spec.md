# Order Status

## Purpose

Provides an authenticated API endpoint that looks up a customer's recent orders by email address, returning order details with nested items and product information.

## Requirements

### Requirement: Order status endpoint accepts email and returns customer orders
The system SHALL expose a `POST /api/order-status` endpoint protected by `ValidateShopToken` middleware. The endpoint SHALL accept an `email` field in the request body, look up the matching `Customer`, and return their latest 5 orders with nested order items and product details. If no customer is found for the given email, the endpoint SHALL return a 404 response.

#### Scenario: Valid email returns orders
- **WHEN** a valid authenticated request is made with an `email` that matches an existing customer
- **THEN** the endpoint returns 200 with the customer's latest 5 orders, each containing `id`, `total_amount`, `created_at`, and an `items` array

#### Scenario: Each item includes product details
- **WHEN** orders are returned
- **THEN** each item in the `items` array contains `quantity`, `price`, and a nested `product` object with `name` and `category`

#### Scenario: Unknown email returns 404
- **WHEN** a valid authenticated request is made with an `email` that does not match any customer
- **THEN** the endpoint returns a 404 response

#### Scenario: Missing email returns validation error
- **WHEN** a valid authenticated request is made without an `email` field in the body
- **THEN** the endpoint returns a 422 validation error

#### Scenario: Unauthenticated request is rejected
- **WHEN** a request to `POST /api/order-status` is made without a valid `X-Laravel-Auth-Token` header
- **THEN** the endpoint returns 401 before reaching the controller logic
