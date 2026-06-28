## ADDED Requirements

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

## MODIFIED Requirements

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

