COMPOSE ?= docker compose
IMGPROXY_PORT ?= 8080
export UID := $(shell id -u)
export GID := $(shell id -g)

.DEFAULT_GOAL := help

.PHONY: help build up down restart logs ps install test pint phpstan shell imgproxy imgproxy-logs imgproxy-test

help: ## Show available targets
	@awk 'BEGIN {FS = ":.*## "} /^[a-zA-Z0-9_-]+:.*## / {printf "  %-16s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build the PHP development image
	$(COMPOSE) build app

up: ## Start the PHP app and ImgProxy
	$(COMPOSE) up -d --build

down: ## Stop and remove containers
	$(COMPOSE) down

restart: down up ## Restart the stack

logs: ## Follow logs for every service
	$(COMPOSE) logs -f

ps: ## Show service status
	$(COMPOSE) ps

install: ## Install Composer dependencies inside the app container
	$(COMPOSE) run --rm --no-deps --user 0:0 -e HOST_UID=$(UID) -e HOST_GID=$(GID) app \
		sh -c 'composer install --prefer-dist --no-interaction && chown -R "$$HOST_UID:$$HOST_GID" vendor'

test: ## Install dependencies when needed, then run PHPUnit
	@if [ ! -x vendor/bin/phpunit ]; then $(MAKE) install; \
	elif [ ! -w vendor/orchestra/testbench-core/laravel/bootstrap/cache ]; then \
		$(COMPOSE) run --rm --no-deps --user 0:0 -e HOST_UID=$(UID) -e HOST_GID=$(GID) app \
			sh -c 'chown -R "$$HOST_UID:$$HOST_GID" vendor'; \
	fi
	$(COMPOSE) run --rm --no-deps app vendor/bin/phpunit

pint: ## Format PHP with Laravel Pint
	@if [ ! -x vendor/bin/pint ]; then $(MAKE) install; fi
	$(COMPOSE) run --rm --no-deps app vendor/bin/pint

phpstan: ## Run PHPStan inside the app container
	@if [ ! -x vendor/bin/phpstan ]; then $(MAKE) install; fi
	$(COMPOSE) run --rm --no-deps app vendor/bin/phpstan analyse --no-progress --memory-limit=1G

shell: ## Open a shell in the app container
	$(COMPOSE) run --rm --no-deps app bash

imgproxy: ## Start only ImgProxy
	$(COMPOSE) up -d imgproxy

imgproxy-logs: ## Follow ImgProxy logs
	$(COMPOSE) logs -f imgproxy

imgproxy-test: imgproxy ## Request a signed image from the local ImgProxy
	IMGPROXY_PORT=$(IMGPROXY_PORT) ./docker/imgproxy/test.sh
