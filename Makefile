.DEFAULT_GOAL := help
UNAME := $(shell uname)
DOCKER_COMPOSE := docker compose
PHP_CLI := $(DOCKER_COMPOSE) run --rm app

.PHONY: help
help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "; printf "Usage: make \033[32m<target>\033[0m\n"}{printf "\033[32m%-15s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m## /\n[33m/'

## Docker
.PHONY: up
up: ## Build and start containers.
	$(DOCKER_COMPOSE) up -d --remove-orphans

.PHONY: down
down: ## Stop and remove containers.
	$(DOCKER_COMPOSE) down --remove-orphans

.PHONY: ps
ps: ## List active containers.
	$(DOCKER_COMPOSE) ps

## GdprDump
.PHONY: dump
dump: .env vendor ## Run bin/gdpr-dump command. Example: "make dump c=test.yaml"
	@$(eval c ?=)
	$(PHP_CLI) bin/gdpr-dump $(c)

.PHONY: compile
compile: .env ## Run bin/compile command.
	$(PHP_CLI) composer install --no-dev
	$(PHP_CLI) bin/compile $(c)
	$(PHP_CLI) composer install

## Composer
.PHONY: composer
composer: ## Run composer. Example: "make composer c=update"
	$(PHP_CLI) composer $(c)

## Code Quality
.PHONY: analyse
analyse: .env vendor ## Run code analysis tools (parallel-lint, phpcs, phpstan).
	$(PHP_CLI) composer audit
	$(PHP_CLI) vendor/bin/parallel-lint src tests
	$(PHP_CLI) vendor/bin/phpcs
	$(PHP_CLI) vendor/bin/phpstan analyse

.PHONY: test
test: .env vendor ## Run phpunit.
	$(PHP_CLI) vendor/bin/phpunit

vendor:
	$(PHP_CLI) composer install

.env:
	@cp .env.dist .env
ifeq ($(UNAME), Linux)
	@sed -i -e "s/^UID=.*/UID=$$(id -u)/" -e "s/^GID=.*/GID=$$(id -g)/" .env
endif
	@echo ".env file was automatically created."
