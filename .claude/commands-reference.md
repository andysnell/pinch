# Common Tasks & Commands

IMPORTANT: This project is dockerized. Do not assume that the host has PHP, Composer, or X installed locally. While you
can manipulate files from the host (e.g. `mkdir`, `mv` and `find`), any language-specific or project-specific tooling
like PHPStan or PHPUnit MUST be run inside the Docker container orchestrated by Docker Compose. Analyze the
`docker-compose.yml` file to understand what services are available. The `php` service is the primary service for
running PHP commands.

IMPORTANT: The project path in the Docker container is `/app`. This does not match the Users' host path. You must be
aware of this when running commands from the host, and adjust accordingly. This is especially important when running
commands from the host that are not part of the Docker Compose workflow, or when writing scripts.

The project also has a Makefile that provides convenient shortcuts for common tasks. It MUST be run from the host,
because it is configured to run commands inside the Docker container.

## PHP

REMEMBER: There is no local PHP installation. All PHP commands MUST be run inside the Docker container orchestrated by
Docker Compose.

```bash
# Run PHP commands inside the Docker container
docker compose run --rm php php [command]
```

## Testing

```bash
# Run all the PHPUnit and Behat tests suites for the monorepo using ParaTest
make test

# Run all the PHPUnit tests suites for the monorepo using ParaTest
make phpunit

# Run all the PHPUnit tests suites for the monorepo, and generate HTML code coverage in `build/phpunit`
make phpunit-coverage

# Run specific PHPUnit package suite (adapt for single file tests)
docker compose run --rm php php vendor/bin/phpunit --testsuite=component

# Enable XDebug code coverage (can adapt with paratest or other output)
docker compose run --rm -e XDEBUG_MODE=coverage php php vendor/bin/phpunit --coverage-html build/phpunit
```

## Static Analysis

```bash
# Run PHPStan static analysis on the whole codebase (uses the "table" output format)
make phpstan

# Run PHPStan static analysis on the whole codebase, using the "json" output format
docker compose run --rm php php -d memory_limit=-1 vendor/bin/phpstan analyze --no-progress --error-format=json
```

## Code Quality

The following commands are always run from the host, and will run on the codebase as a whole. This is desired behavior,
as it allows us to catch issues in other packages that may be affected by changes in the current package. If you want to
run these commands on a specific package, analyze the `Makefile` recipe.

```bash
# Run PHP Syntax linting
make lint

# Run PHP CodeSniffer Code Beautification (ALWAYS RUN AFTER PHPCBF and via Makefile)
make phpcbf

# Run PHP CodeSniffer Code Style Check (ALWAYS RUN AFTER PHPCBF and via Makefile)
make phpcs

# Run Prettier Code Formatter for Non-PHP Code
make prettier-write

# Run Prettier Code Checker for Non-PHP Code
make prettier-check

```

## Other Development

```bash
# PHP
docker compose run --rm php php [command]

# Composer
docker compose run --rm php composer [command]

# Profile with XDebug
docker compose run --rm -e XDEBUG_MODE=profile php

# Clean generated files - IMPORTANT Only run this after getting explicit approval from the User to do so
make clean
```
