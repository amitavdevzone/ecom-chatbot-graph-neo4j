# Typesense Docker Infrastructure

## Purpose

Defines the Docker Compose services required to run Typesense search engine and its dashboard UI as part of the local development environment, including network connectivity, data persistence, and API key configuration.

## Requirements

### Requirement: Typesense search engine service
The Docker Compose setup SHALL include a `typesense` service running the latest Typesense image, accessible on port `8108`, with data persisted in a named volume and connected to the `ecom-graph-db` network.

#### Scenario: Typesense starts successfully
- **WHEN** a developer runs `docker compose up`
- **THEN** the `typesense` container starts and its health check at `/health` returns HTTP 200

#### Scenario: Typesense data persists across restarts
- **WHEN** the `typesense` container is stopped and restarted
- **THEN** previously created collections and documents are still available

#### Scenario: Laravel app can reach Typesense by hostname
- **WHEN** the Laravel app container makes an HTTP request to `http://typesense:8108`
- **THEN** the request succeeds because both services share the `ecom-graph-db` network

### Requirement: Typesense Dashboard UI service
The Docker Compose setup SHALL include a `typesense-dashboard` service using the `bfritscher/typesense-dashboard` image, accessible on port `8109`, connected to the `ecom-graph-db` network.

#### Scenario: Dashboard is reachable in the browser
- **WHEN** a developer navigates to `http://localhost:8109`
- **THEN** the Typesense Dashboard SPA loads in the browser

#### Scenario: Dashboard connects to the local Typesense node
- **WHEN** a developer enters `http://localhost:8108` as the Typesense URL and the configured API key in the dashboard login screen
- **THEN** the dashboard displays the Typesense node's collections and metrics

### Requirement: Typesense API key configuration
The `typesense` service SHALL accept a configurable API key via the `TYPESENSE_API_KEY` environment variable, defaulting to a static dev key in `docker-compose.yml`.

#### Scenario: Default API key is set
- **WHEN** no `.env` override is provided
- **THEN** the Typesense service starts with the default API key defined in `docker-compose.yml`

#### Scenario: API key can be overridden
- **WHEN** a developer sets `TYPESENSE_API_KEY` in their local `.env` file
- **THEN** `docker compose up` passes the overridden key to the Typesense container
