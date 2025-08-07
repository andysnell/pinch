SHELL := bash
.SHELLFLAGS = -ec
.DEFAULT_GOAL := build/.install
.WAIT:

_WARN := "\033[33m%s\033[0m %s\n"  # Yellow text template for "printf"
_INFO := "\033[32m%s\033[0m %s\n" # Green text template for "printf"
_ERROR := "\033[31m%s\033[0m %s\n" # Red text template for "printf"

##------------------------------------------------------------------------------
# Command Aliases & Function/Variable Definitions
##------------------------------------------------------------------------------

docker-php = docker compose run --rm php
docker-run = docker run --rm --env-file "$${PWD}/.env" --user=$$(id -u):$$(id -g)

# Define behavior to safely source file (1) to dist file (2), without overwriting
# if the dist file already exists. This is more portable than using `cp --no-clobber`.
define copy-safe
	if [ ! -f "$(2)" ]; then \
		echo "Copying $(1) to $(2)"; \
		cp "$(1)" "$(2)"; \
	else \
		echo "$(2) already exists, not overwriting."; \
	fi
endef

# Define behavior to check if a token (1) is set in .env, and prompt user to set it if not.
# If the token is already set, inform the user. If the token name is not found in .env,
# it will be appended, otherwise, the existing value will be updated.
define check-token
	if grep -q "^$(1)=" ".env"; then \
		TOKEN_VALUE=$$(grep "^$(1)=" ".env" | cut -d'=' -f2); \
		if [ -z "$$TOKEN_VALUE" ]; then \
			read -p "Please enter your $(1): " NEW_TOKEN; \
			sed -i "s/^$(1)=.*/$(1)=$$NEW_TOKEN/" ".env"; \
			echo "$(1) updated successfully!"; \
		else \
			echo "$(1) is already set."; \
		fi; \
	else \
		read -p "$(1) not found. Please enter your $(1): " NEW_TOKEN; \
		echo -e "\n$(1)=$$NEW_TOKEN" >> ".env"; \
		echo "$(1) added successfully!"; \
	fi
endef

BUILD_DIRS = build/.phpunit.cache \
	build/composer \
	build/docker \
	build/phpstan \
	build/phpunit \
	build/psysh/config \
	build/psysh/data \
	build/psysh/tmp \
	build/rector \
	build/xdebug

##------------------------------------------------------------------------------
# Docker Targets
##------------------------------------------------------------------------------

build/docker/docker-compose.json: packages/template/Dockerfile compose.yaml | build/docker
	docker compose pull --quiet --policy="always"
	COMPOSE_BAKE=true docker compose build \
		--pull \
		--build-arg USER_UID=$$(id -u) \
		--build-arg USER_GID=$$(id -g)
	touch "$@" # required to consistently update the file mtime

build/docker/pinch-%.json: packages/template/Dockerfile | build/docker
	docker buildx build --target="$*" --pull --load --tag="pinch-$*" --file packages/template/Dockerfile .
	docker image inspect "pinch-$*" > "$@"

##------------------------------------------------------------------------------
# Build/Setup/Teardown Targets
##------------------------------------------------------------------------------

.env:
	@$(call copy-safe,.env.dist,.env)

phpstan.neon:
	@$(call copy-safe,phpstan.dist.neon,phpstan.neon)

phpunit.xml:
	@$(call copy-safe,phpunit.dist.xml,phpunit.xml)

$(BUILD_DIRS): | .env
	mkdir --parents "$@"

vendor: build/composer build/docker/docker-compose.json composer.json composer.lock | .env
	mkdir --parents "$@"
	@$(call check-token,GITHUB_TOKEN)
	$(docker-php) composer install
	@touch vendor

build/.install : vendor build/docker/pinch-prettier.json | $(BUILD_DIRS)
	@echo "Application Build Complete."
	@touch build/.install

.PHONY: clean
clean:
	$(docker-php) rm -rf ./build ./vendor

##------------------------------------------------------------------------------
# Code Quality, Testing & Utility Targets
##------------------------------------------------------------------------------

.PHONY: up
up:
	docker compose up --detach

.PHONY: down
down:
	docker compose down --remove-orphans

.PHONY: bash
bash: build/docker/docker-compose.json
	$(docker-php) bash

.PHONY: shell psysh
shell psysh: build/.install
	docker compose up --detach
	$(docker-php) packages/template/bin/pinch shell

.PHONY: lint phpcbf phpcs phpstan phpunit phpunit-coverage rector rector-dry-run test behat paratest paratest-coverage
lint phpcbf phpcs phpstan phpunit phpunit-coverage rector rector-dry-run test behat paratest paratest-coverage: build/.install
	$(docker-php) composer run-script "$@"

.NOTPARALLEL: ci pre-ci preci
.PHONY: ci pre-ci preci
ci: lint phpcs phpstan paratest prettier-check rector-dry-run

.NOTPARALLEL: pre-ci preci
.PHONY: pre-ci preci
pre-ci preci: prettier-write rector phpcbf ci

# Run the PHP development server to serve the HTML test coverage report on port 8000.
.PHONY: serve-coverage
serve-coverage:
	@docker compose run --rm --publish 8000:80 php php -S 0.0.0.0:80 -t /app/build/phpunit

##------------------------------------------------------------------------------
# Prettier Code Formatter for JSON, YAML, HTML, Markdown, and CSS Files
# Example Usage: `make prettier-check`, `makeprettier-write`
##------------------------------------------------------------------------------

.PHONY: prettier-%
prettier-%: | build/docker/pinch-prettier.json
	$(docker-run) --volume $${PWD}:/app pinch-prettier --$* .

##------------------------------------------------------------------------------
# Enable Makefile Overrides
#
# If a "build/Makefile" exists, it can define additional targets/behavior and/or
# override the targets of this Makefile. Note that this declaration has to occur
# at the end of the file in order to effect the override behavior.
##------------------------------------------------------------------------------

-include build/Makefile
-include ./local/Makefile
