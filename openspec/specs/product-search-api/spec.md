## Purpose

Defines the behaviour of the product search API endpoint, which allows authenticated clients to query products by free-text against name and description fields.
## Requirements
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

### Requirement: Product search delegates to Typesense
The `ProductSearchController` SHALL use `Product::search($query)` to perform full-text search via Typesense. When `filter_by` or `sort_by` fields are present in the request they SHALL be forwarded to Typesense via the Scout builder's `options()` method. The search result SHALL be returned as a `ProductResource` collection. The controller SHALL NOT apply a hardcoded query override.

#### Scenario: Search returns matching products from Typesense
- **WHEN** a POST request is made to `/api/products/search` with a valid `query` parameter
- **THEN** `Product::search($query)` is called and matching products are returned as a JSON resource collection

#### Scenario: filter_by is forwarded to Typesense options
- **WHEN** the request includes a `filter_by` field
- **THEN** the Scout builder is called with `options(['filter_by' => $filterBy])` and Typesense applies the filter

#### Scenario: sort_by is forwarded to Typesense options
- **WHEN** the request includes a `sort_by` field
- **THEN** the Scout builder is called with `options(['sort_by' => $sortBy])` and Typesense applies the sort

#### Scenario: Missing query returns validation error
- **WHEN** a POST request is made without the `query` field
- **THEN** a 422 validation error is returned

### Requirement: Search accepts optional filter_by and sort_by parameters
The system SHALL accept optional `filter_by` and `sort_by` string fields in the POST body of `/api/products/search` and forward them to Typesense alongside the `query`.

#### Scenario: filter_by is applied when provided
- **WHEN** the request body includes a valid `filter_by` string (e.g., `"price:<1000"`)
- **THEN** Typesense filters results to only products matching that expression and the response is HTTP 200 with a `data` array of matching products

#### Scenario: sort_by is applied when provided
- **WHEN** the request body includes a valid `sort_by` string (e.g., `"price:asc"`)
- **THEN** Typesense returns products sorted by that expression and the response is HTTP 200 with products in the specified order

#### Scenario: Both filter_by and sort_by are applied together
- **WHEN** the request body includes both `filter_by` and `sort_by`
- **THEN** Typesense applies both the filter and the sort and the response is HTTP 200 with filtered, sorted products

#### Scenario: filter_by and sort_by are omitted
- **WHEN** the request body contains only `query` with no `filter_by` or `sort_by`
- **THEN** the search runs as a plain keyword query with no additional filtering or sorting

