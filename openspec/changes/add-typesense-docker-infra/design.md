## Context

The project currently runs PostgreSQL, pgAdmin, Neo4j, and the Laravel app as Docker services on a shared `ecom-graph-db` bridge network. Typesense is a fast, typo-tolerant search engine that will serve as the search backend. The Typesense Dashboard (`bfritscher/typesense-dashboard`) is a lightweight static web app that connects directly to a Typesense node from the browser, making it a zero-config companion for local development.

## Goals / Non-Goals

**Goals:**
- Add a `typesense` service using the official `typesensio/typesense` latest image
- Add a `typesense-dashboard` service using `bfritscher/typesense-dashboard` for browser-based collection management
- Persist Typesense data in a named Docker volume
- Expose both services on predictable local ports
- Wire both services into the existing Docker network so the Laravel app can reach Typesense by hostname

**Non-Goals:**
- Laravel application integration (Scout driver configuration, indexing logic) — deferred to a follow-up change
- Production deployment configuration
- Authentication hardening beyond the dev API key

## Decisions

### Typesense image tag
Use `typesense/typesense:latest` to always pull the newest stable release in development. A pinned tag should be used when moving to production.

### API key
Use a static dev key (`typesense-api-key`) set via the `TYPESENSE_API_KEY` environment variable. This is acceptable for local development; it will be overridden by an env-file or secret manager in production.

### Ports
- Typesense: `8108:8108` — the default Typesense HTTP port, keeps host port identical to container port for intuitive `.env` config (`TYPESENSE_PORT=8108`).
- Typesense Dashboard: `8109:80` — avoids collision with any existing service; the dashboard's nginx listens on port 80 inside the container.

### Health check
Use `wget -q --spider http://localhost:8108/health` inside the Typesense container, consistent with how Neo4j is health-checked in this compose file.

### Dashboard connectivity
The `typesense-dashboard` container serves a static SPA. The browser connects to Typesense directly using `http://localhost:8108`, so the dashboard does not need a backend service dependency — it only needs to be on the same Docker network for any future server-side use.

## Risks / Trade-offs

- **`latest` tag pulls a new image on every `docker compose pull`** → Acceptable in development; note it in the README when adding production config.
- **API key is visible in `docker-compose.yml`** → Standard practice for local dev environments; do not commit production keys.
- **Port 8108 / 8109 conflicts with host services** → Developers with conflicting port bindings can override via a `.env` file using `TYPESENSE_PORT` / `TYPESENSE_DASHBOARD_PORT` variables once compose variable substitution is applied.
