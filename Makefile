.DEFAULT_GOAL := help

.PHONY: help art

help: ## Show available commands
	@grep -E '^[a-zA-Z0-9_-]+:.*?(## .*)?$$' $(MAKEFILE_LIST) | \
		grep -v '^help:' | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, ($$2 == "" ? "" : $$2)}'

art: ## Run artisan in the app container (e.g. make art ARGS="migrate")
	docker compose exec app php artisan $(ARGS)
