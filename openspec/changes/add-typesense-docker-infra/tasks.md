## 1. Docker Compose — Typesense Service

- [ ] 1.1 Add the `typesense` service to `docker-compose.yml` using the `typesense/typesense:latest` image, port `8108:8108`, `TYPESENSE_API_KEY` env var, and data directory argument pointing to `/data`
- [ ] 1.2 Add a `typesense_data` named volume and mount it at `/data` inside the `typesense` container
- [ ] 1.3 Add a health check for the `typesense` service using `wget -q --spider http://localhost:8108/health`
- [ ] 1.4 Connect the `typesense` service to the `ecom-graph-db` network

## 2. Docker Compose — Typesense Dashboard Service

- [ ] 2.1 Add the `typesense-dashboard` service to `docker-compose.yml` using the `bfritscher/typesense-dashboard` image, port `8109:80`
- [ ] 2.2 Connect the `typesense-dashboard` service to the `ecom-graph-db` network

## 3. Verification

- [ ] 3.1 Run `docker compose up` and confirm both `typesense` and `typesense-dashboard` containers start without errors
- [ ] 3.2 Confirm Typesense health endpoint responds: `curl http://localhost:8108/health`
- [ ] 3.3 Open `http://localhost:8109` in a browser and confirm the Typesense Dashboard UI loads
- [ ] 3.4 Log into the dashboard using `http://localhost:8108` as the node URL and the configured API key, and confirm it connects successfully
