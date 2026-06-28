# E-Com Website with AI Chatbot and Neo4j Graph DB

A Laravel e-commerce demo application that pairs a traditional relational
database (PostgreSQL) with a graph database (Neo4j) to power an AI chatbot
that gives genuinely personalized product recommendations — based on what a
customer has bought, what they've liked, and what offers they're eligible
for.

## Application URLs

Once the stack is running via Docker, the following are available:

| Service | URL | Notes |
|---|---|---|
| Application | http://localhost:8000 | Storefront / Filament admin at `/admin` |
| Recommendation chatbot | http://localhost:8000/recommendations | AI-powered product recommendations |
| Neo4j browser | http://localhost:7474 | Graph database UI (user: `neo4j`, password: `password`) |
| PG Admin | http://localhost:5050 | PostgreSQL UI (login: `admin@localhost.com` / `admin`) |
| Typesense | http://localhost:8108 | Search engine API (API key: `typesense-api-key` by default) |
| Typesense Dashboard | http://localhost:8109 | Search engine UI |

## Tech Stack

- **Laravel 13** + **Filament 5** (admin panel)
- **PostgreSQL 16** — relational data (customers, products, orders, offers)
- **Neo4j 5** (with APOC) — graph data for recommendations
- **Typesense 30** + **Laravel Scout** — full-text product search with filter and sort support
- **NeuronAI** + **OpenRouter** — the AI agent powering the chatbot
- **Docker Compose** — local development environment

## Setup with Docker

1. **Clone the repository and copy the environment file**

   ```bash
   cp .env.example .env
   ```

2. **Add your OpenRouter API key** to `.env` (required for the recommendation chatbot):

   ```
   OPENROUTER_API_KEY=your-key-here
   ```

3. **Build and start the containers**

   ```bash
   docker compose up -d --build
   ```

   This starts four services:
   - `app` — the Laravel application (PHP-FPM + Nginx)
   - `postgres` — relational database
   - `pgadmin` — UI for PostgreSQL
   - `neo4j` — graph database (Bolt + browser UI)

   Migrations and the storage symlink run automatically on container start
   (`AUTORUN_LARAVEL_MIGRATION` / `AUTORUN_LARAVEL_STORAGE_LINK`).

4. **Seed the relational database** with demo data:

   ```bash
   docker compose exec app php artisan db:seed
   ```

   (or use `make art ARGS="db:seed"`)

5. **Sync the data into Neo4j** to build the recommendation graph:

   ```bash
   docker compose exec app php artisan sync:neo4j
   ```

   This wipes and rebuilds the graph from the relational data — customers,
   products, purchases, likes, and pre-computed offer eligibility.

6. **Sync products into Typesense** to build the search index:

   ```bash
   docker compose exec app php artisan typesense:sync-products
   ```

   This creates the `products` collection if it doesn't exist, then upserts
   all products. Safe to run multiple times. After this, the product search
   API and the agentic app's query-builder can both query Typesense.

7. Visit **http://localhost:8000/recommendations** to chat with the AI
   recommendation assistant.

> A `Makefile` helper is provided — run any artisan command with
> `make art ARGS="your command here"`.

## How Neo4j Powers the Chatbot

The PostgreSQL database remains the source of truth for customers, products,
orders, and offers. Neo4j stores the same entities as a **graph** —
nodes for `Customer`, `Product`, and `Offer`, connected by relationships
like `PURCHASED`, `LIKED`, and `ELIGIBLE_FOR`. This makes "how is X connected
to Y" questions — the kind a recommendation engine needs to answer
constantly — fast and simple to express.

### Keeping the graph in sync

- **`GraphSyncService`** (`app/Services/GraphSyncService.php`) — syncs nodes
  and relationships, and pre-computes offer eligibility using three patterns:
  conversion offers, bundle offers, and loyalty offers.
- **Model observers** (`CustomerObserver`, `OrderObserver`,
  `ProductLikeObserver`) — push incremental updates to the graph in real
  time as customers register, place orders, or like products.
- **`sync:neo4j` artisan command** (`app/Console/Commands/SyncToNeo4j.php`)
  — full wipe-and-rebuild of the graph, useful for setup or recovery.

### The recommendation agent and its tools

**`RecommendationAgent`** (`app/Neuron/RecommendationAgent.php`) is a
NeuronAI agent (via OpenRouter) that orchestrates a set of Neo4j-backed
tools, each extending the shared **`Neo4jRecommendationTool`** base class
(`app/Neuron/Tools/Neo4jRecommendationTool.php`):

- **`CustomerPurchaseHistoryTool`** — what the customer has bought before.
- **`CustomerLikedProductsTool`** — what the customer has liked / wishlisted.
- **`AlsoPurchasedTool`** — "customers who bought this also bought..." via a
  2-hop graph traversal (`PURCHASED` → `PURCHASED`).
- **`LikedAlsoBoughtTool`** — same idea, starting from liked products to
  surface intent-based recommendations.
- **`CustomerEligibleOffersTool`** — reads pre-computed `ELIGIBLE_FOR`
  relationships to instantly show offers a customer qualifies for.
- **`OfferEligibilityTool`** — checks whether a customer qualifies for a
  specific offer.

The agent decides which tools to call and in what order, then summarizes the
results as a structured markdown response in the chat UI.

### Why a graph database here?

Questions like "what should this customer buy next, based on what similar
customers bought and what offers they qualify for" require traversing
several relationships deep. In a relational database that means layered
joins, subqueries, and aggregations. In Neo4j, the same question is a short,
readable Cypher pattern — which keeps both the queries and the AI tools that
wrap them simple.

## How Typesense Powers Product Search

Typesense provides fast, typo-tolerant full-text search over the product catalogue, running as a Docker service alongside the rest of the stack.

### Indexing

The `Product` model uses the `Laravel\Scout\Searchable` trait. Scout observers automatically keep the Typesense `products` collection in sync as products are created, updated, or deleted. The indexed document includes `name`, `description` (searchable), `category` (facetable), `price` (float, sortable and filterable), and timestamps.

For a full wipe-and-reindex, run:

```bash
php artisan typesense:sync-products
```

### Product Search API

`POST /api/products/search` — requires `X-Laravel-Auth-Token` header.

| Field | Type | Required | Description |
|---|---|---|---|
| `query` | string | yes | Free-text search against name and description |
| `filter_by` | string | no | Typesense filter expression, e.g. `price:<1000` |
| `sort_by` | string | no | Typesense sort expression, e.g. `price:asc` |

Returns up to 10 matching products as `{ data: [{ id, name, price, description }] }`.

The `filter_by` and `sort_by` fields are forwarded directly to Typesense via Scout's `options()` method, enabling the agentic app's LLM query-builder to produce structured, intent-aware searches rather than plain keyword queries.
