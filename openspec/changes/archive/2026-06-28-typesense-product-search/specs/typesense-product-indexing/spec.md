## ADDED Requirements

### Requirement: Product model defines Typesense collection schema
The `Product` model SHALL define a static `typesenseCollectionSchema()` method that returns the schema for the `products` Typesense collection. The schema SHALL include: `id` (string), `name` (string, searchable), `description` (string, searchable, optional), `category` (string, facet), `price` (float), `created_at` (int32), `updated_at` (int32, optional).

#### Scenario: Schema method returns correct structure
- **WHEN** `Product::typesenseCollectionSchema()` is called
- **THEN** it returns an array with `name` set to `"products"` and a `fields` array containing all required field definitions

#### Scenario: description field is optional
- **WHEN** the schema is inspected
- **THEN** the `description` field has `'optional' => true`

#### Scenario: category field is facetable
- **WHEN** the schema is inspected
- **THEN** the `category` field has `'facet' => true`

### Requirement: Product model serialises to a searchable document
The `Product` model SHALL define a `toSearchableArray()` method returning a document shaped for Typesense. The `id` field SHALL be cast to string. The `category` field SHALL use the enum's string value (lowercase). The `price` field SHALL be cast to float. Timestamps SHALL be cast to Unix int32. `description` and `updated_at` SHALL be included as `null` when absent.

#### Scenario: Fully populated product serialises correctly
- **WHEN** a product with all fields set is serialised via `toSearchableArray()`
- **THEN** `id` is a string, `category` is the lowercase enum value, `price` is a float, `created_at` and `updated_at` are integers

#### Scenario: Product with null description serialises without error
- **WHEN** a product with `description = null` is serialised
- **THEN** `description` is `null` in the returned array and no exception is thrown

### Requirement: Product model uses Laravel Scout Searchable trait
The `Product` model SHALL use the `Laravel\Scout\Searchable` trait so that Scout lifecycle observers automatically sync the Typesense index on model create, update, and delete.

#### Scenario: New product is indexed after creation
- **WHEN** a new `Product` is saved to the database
- **THEN** Scout queues or immediately calls the Typesense upsert for that document

#### Scenario: Updated product is re-indexed
- **WHEN** an existing `Product` is updated and saved
- **THEN** Scout queues or immediately calls the Typesense upsert with the updated document

#### Scenario: Deleted product is removed from index
- **WHEN** a `Product` is deleted
- **THEN** Scout queues or immediately removes the document from the Typesense collection
