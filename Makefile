.DEFAULT_GOAL := help
UNAME := $(shell uname)
DOCKER_COMPOSE := docker compose
PHP_CLI := $(DOCKER_COMPOSE) run --rm app

include .env

.PHONY: help
help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##)|(^##)' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "; printf "Usage: make \033[32m<target>\033[0m\n"}{printf "\033[32m%-20s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m## /\n[33m/'

## Docker
.PHONY: up
up: ## Build and start containers.
	$(DOCKER_COMPOSE) up -d --remove-orphans $(service)

.PHONY: down
down: ## Stop and remove containers.
	$(DOCKER_COMPOSE) down --remove-orphans

.PHONY: ps
ps: ## List active containers.
	$(DOCKER_COMPOSE) ps

.PHONY: build
build: ## Build images.
	$(DOCKER_COMPOSE) build

## GdprDump
.PHONY: dump
dump: vendor ## Run bin/gdpr-dump command. Example: "make dump c=test.yaml"
	@$(eval c ?=)
	$(PHP_CLI) bin/gdpr-dump $(c)

.PHONY: compile
compile: ## Run bin/compile command.
	$(PHP_CLI) composer install --no-dev
	$(PHP_CLI) bin/compile $(c)
	$(PHP_CLI) composer install

## Composer
.PHONY: composer
composer: ## Run composer. Example: "make composer c=update"
	$(PHP_CLI) composer $(c)

## Code Quality
.PHONY: analyse
analyse: vendor ## Run code analysis tools (parallel-lint, phpcs, phpstan).
	$(PHP_CLI) composer audit
	$(PHP_CLI) vendor/bin/parallel-lint app bin src tests
	$(PHP_CLI) vendor/bin/phpcs
	$(PHP_CLI) vendor/bin/phpstan analyse

.PHONY: test
test: vendor ## Run phpunit.
	$(PHP_CLI) vendor/bin/phpunit

## Database
.PHONY: db
db: service := --wait db
db: up ## Connect to the database.
	$(DOCKER_COMPOSE) exec db sh -c 'mysql --password=$$MYSQL_ROOT_PASSWORD'

.PHONY: db-import
db-import: service := --wait db
db-import: up ## Execute a SQL file. Pass the parameter "filename=" to set the filename (default: dump.sql).
	$(eval filename ?= dump.sql)
	$(DOCKER_COMPOSE) exec -T db sh -c 'mysql --password=$$MYSQL_ROOT_PASSWORD' < $(filename)

vendor: composer.json
	$(PHP_CLI) composer install

.env: | .env.dist
	@cp .env.dist .env
ifeq ($(UNAME), Linux)
	@sed -i -e "s/^UID=.*/UID=$$(id -u)/" -e "s/^GID=.*/GID=$$(id -g)/" .env
endif
	@echo ".env file was automatically created."