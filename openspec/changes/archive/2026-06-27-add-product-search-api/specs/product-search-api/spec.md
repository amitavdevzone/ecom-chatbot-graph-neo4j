## ADDED Requirements

### Requirement: Search products by free-text query
The system SHALL accept a POST request to `/api/products/search` with a `query` string and return up to 10 products whose `name` or `description` matches the query case-insensitively.

#### Scenario: Matching products are found
- **WHEN** a valid `query` matches one or more product names or descriptions
- **THEN** the response is HTTP 200 with a `data` array of up to 10 products, each containing `id`, `name`, `price`, and `description`

#### Scenario: No products match the query
- **WHEN** a valid `query` matches no products
- **THEN** the response is HTTP 200 with an empty `data` array

#### Scenario: Query matches name but not description
- **WHEN** the query string appears in a product's `name` but not its `description`
- **THEN** that product is included in the results

#### Scenario: Query matches description but not name
- **WHEN** the query string appears in a product's `description` but not its `name`
- **THEN** that product is included in the results

#### Scenario: Result count is capped at 10
- **WHEN** more than 10 products match the query
- **THEN** exactly 10 products are returned

### Requirement: Endpoint requires valid shop token authentication
The system SHALL reject requests to `/api/products/search` that do not provide a valid `X-Laravel-Auth-Token` header.

#### Scenario: Missing auth token
- **WHEN** the request has no `X-Laravel-Auth-Token` header
- **THEN** the response is HTTP 401

#### Scenario: Invalid auth token
- **WHEN** the request carries an incorrect `X-Laravel-Auth-Token` value
- **THEN** the response is HTTP 401

#### Scenario: Valid auth token
- **WHEN** the request carries the correct `X-Laravel-Auth-Token` value
- **THEN** the request proceeds and the search is executed

### Requirement: Query field is required
The system SHALL validate that the `query` field is present and non-empty in the request body.

#### Scenario: Missing query field
- **WHEN** the request body does not include a `query` field
- **THEN** the response is HTTP 422 with a validation error on `query`

#### Scenario: Empty query string
- **WHEN** the `query` field is an empty string
- **THEN** the response is HTTP 422 with a validation error on `query`
