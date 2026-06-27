# Shop Token Auth

## Purpose

Defines middleware-level authentication for protected API endpoints using a shared secret token passed via the `X-Laravel-Auth-Token` request header.

## Requirements

### Requirement: Requests must carry a valid shop token
The system SHALL validate all protected API requests by checking the `X-Laravel-Auth-Token` request header against `config('services.laravel-shop-token.access_token')`. Requests with a missing or mismatched token SHALL be rejected with a 401 response.

#### Scenario: Valid token is accepted
- **WHEN** a request includes an `X-Laravel-Auth-Token` header whose value matches `config('services.laravel-shop-token.access_token')`
- **THEN** the middleware allows the request to proceed to the controller

#### Scenario: Missing token is rejected
- **WHEN** a request does not include the `X-Laravel-Auth-Token` header
- **THEN** the middleware returns a 401 JSON response with `{"message": "Unauthorized"}`

#### Scenario: Invalid token is rejected
- **WHEN** a request includes an `X-Laravel-Auth-Token` header whose value does not match the configured secret
- **THEN** the middleware returns a 401 JSON response with `{"message": "Unauthorized"}`
