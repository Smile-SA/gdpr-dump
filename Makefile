UNAME := $(shell uname)
PHP_CLI := docker compose run --rm app

default: help

.PHONY: help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "Usage: make \033[32m<target>\033[0m\n\n"} /^[a-zA-Z_-]+:.*?##/ { printf "\033[32m%-15s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

.PHONY: gdpr-dump
gdpr-dump: .env vendor ## Run bin/gdpr-dump command. Example: make gdpr-dump args=test.yaml
	@$(eval args ?=)
	$(PHP_CLI) bin/gdpr-dump $(args)

.PHONY: analyse
analyse: .env vendor ## Run code analysis tools (phpcs, phpstan).
	$(PHP_CLI) vendor/bin/phpcs && vendor/bin/phpstan analyse

.PHONY: test
test: .env vendor ## Run phpunit.
	$(PHP_CLI) vendor/bin/phpunit

vendor:
	$(PHP_CLI) composer install

.env:
ifeq ($(UNAME), Linux)
	@sed -e "s/^UID=.*/UID=$$(id -u)/" -e "s/^GID=.*/GID=$$(id -g)/" .env.example > .env
	@echo ".env file was created with UID=$$(id -u) and GID=$$(id -g)"
endif
