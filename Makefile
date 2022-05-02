PHP_CLI := docker compose run --rm app

default: help

.PHONY: help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "Usage: make \033[32m<target>\033[0m\n\n"} /^[a-zA-Z_-]+:.*?##/ { printf "\033[32m%-15s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

.PHONY: gdpr-dump
gdpr-dump: ## Run bin/gdpr-dump command. Example: make gdpr-dump args=test.yaml
	@$(eval args ?=)
	$(PHP_CLI) bin/gdpr-dump $(args)

.PHONY: install
install: ## Run composer install.
	$(PHP_CLI) composer install

.PHONY: update
update: ## Run composer update.
	$(PHP_CLI) composer update

.PHONY: analyze
analyze: ## Run code analysis tools (phpcs, phpstan).
	$(PHP_CLI) vendor/bin/phpcs && vendor/bin/phpstan analyse

.PHONY: test
test: ## Run phpunit.
	$(PHP_CLI) vendor/bin/phpunit
