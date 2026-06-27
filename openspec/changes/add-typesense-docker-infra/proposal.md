## Why

The project needs full-text and faceted search capabilities for the e-commerce domain. Adding Typesense to the Docker development environment establishes the search infrastructure so the application can be wired up to it in subsequent changes.

## What Changes

- Add a `typesense` service (latest image) to `docker-compose.yml` with persistent volume and health check
- Add a `typesense-dashboard` service using the `bfritscher/typesense-dashboard` image for a browser-based UI to inspect and manage Typesense collections
- Expose Typesense on port `8108` (its default) and the dashboard on a convenient local port (e.g. `8109`)
- Connect both new services to the existing `ecom-graph-db` Docker network

## Capabilities

### New Capabilities

- `typesense-docker-infra`: Typesense search engine and its management dashboard running as Docker services in the local development environment

### Modified Capabilities

<!-- None - this change is purely infrastructure; no existing spec-level behaviour changes -->

## Impact

- `docker-compose.yml`: two new services (`typesense`, `typesense-dashboard`) and one new named volume (`typesense_data`)
- No application code changes in this phase
- Developers will need to re-run `docker compose up` to pull the new images
