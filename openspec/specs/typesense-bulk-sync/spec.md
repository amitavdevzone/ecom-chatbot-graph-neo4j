## Purpose

Defines the behaviour of the Artisan command that performs a full bulk sync of all products from the database into the Typesense search index.

## Requirements

### Requirement: Artisan command syncs all products to Typesense
The system SHALL provide a `typesense:sync-products` Artisan command that creates the Typesense `products` collection if it does not exist, then upserts all products. The command SHALL be idempotent — safe to run multiple times and after bulk database updates.

#### Scenario: Command runs against empty Typesense
- **WHEN** `php artisan typesense:sync-products` is run and no `products` collection exists
- **THEN** the collection is created and all products are upserted into it

#### Scenario: Command runs when collection already exists
- **WHEN** `php artisan typesense:sync-products` is run and the `products` collection already exists
- **THEN** all products are upserted without dropping or recreating the collection

#### Scenario: Existing product is overridden on re-sync
- **WHEN** a product's fields have been changed in the database and the command is run
- **THEN** the updated document replaces the previous one in the Typesense collection

#### Scenario: Command exits with success code
- **WHEN** the command completes without error
- **THEN** it exits with `Command::SUCCESS` (0)

### Requirement: Command output communicates progress
The command SHALL output status messages at the start and end of the sync, following the style of the existing `SyncToNeo4j` command (using `$this->components->info()` and `$this->components->success()`).

#### Scenario: User sees sync start message
- **WHEN** the command begins execution
- **THEN** an info message is displayed indicating the sync has started

#### Scenario: User sees sync complete message
- **WHEN** the command finishes successfully
- **THEN** a success message is displayed confirming completion
