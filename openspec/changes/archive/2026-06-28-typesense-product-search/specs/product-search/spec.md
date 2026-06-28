## MODIFIED Requirements

### Requirement: Product search delegates to Typesense
The `ProductSearchController` SHALL use `Product::search($query)` to perform full-text search via Typesense instead of querying PostgreSQL directly with `LIKE`. The search result SHALL be returned as a `ProductResource` collection. The query input validation (required string) SHALL remain unchanged.

#### Scenario: Search returns matching products from Typesense
- **WHEN** a GET request is made to the product search endpoint with a valid `query` parameter
- **THEN** `Product::search($query)` is called and matching products are returned as a JSON resource collection

#### Scenario: Hardcoded query override remains in place
- **WHEN** any `query` value is submitted
- **THEN** the controller overrides it with `"mobile"` (temporary, to be removed in a later change)

#### Scenario: Missing query returns validation error
- **WHEN** a GET request is made without the `query` parameter
- **THEN** a 422 validation error is returned
