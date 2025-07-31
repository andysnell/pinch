# Development Workflow

## Development Environment

IMPORTANT: This project is dockerized. Do not assume that the host has PHP, Composer, or X installed locally. While you
can manipulate files from the host (e.g. `mkdir`, `mv` and `find`), any language-specific or project-specific tooling
like PHPStan or PHPUnit MUST be run inside of the Docker container orchestrated by Docker Compose. Analyze the
`docker-compose.yml` file to understand what services are available. The `php` service is the primary service for
running PHP commands.

IMPORTANT: When creating scripts or running commands, try to keep file paths relative to the project root. Because the
project executables (e.g. PHP) run inside Docker containers, using absolute paths from the host will cause failures.

IMPORTANT: If you need to create a temporary or intermediate file or script, you should put it in the project
`build/claude` directory (relative to the project root). If the directory does not exist, you may create it.

IMPORTANT: If you need to create a helper script that is not temporary in nature, but also does not belong under version
control, you should put it in the project `.local/claude` directory (relative to the project root). If the directory
does not exist, you may create it.

## Development Workflow Steps

1. Always check existing patterns in neighboring files before implementing
2. Use the Read tool to understand file conventions before editing
3. Run tests after making changes
4. Check for component-specific exceptions in `Exception/` subdirectories

## Request Acceptance Criteria

Before marking any task as complete, ensure ALL of the following criteria are met:

### Functional Requirements

- [ ] New service classes that will be injected into other services MUST be registered in a service provider
- [ ] New service classes MUST NOT be marked `final`, but may have final methods or constants
- [ ] New services MUST NOT be marked `readonly`, but may have readonly properties
- [ ] When changing **any** existing code, all usages, both inside and outside the immediate package namespace MUST be
      considered and updated if necessary, e.g., if a class in the `components` package is changed, the `framework` and
      `template` packages MUST be updated to reflect those changes
- [ ] Classes or interfaces with the `#[\PhoneBurner\Pinch\Attribute\StableClassName]` attribute MUST NOT have their
      fully qualified class name changed without explicit approval
- [ ] Classes or interfaces with the `#[\PhoneBurner\Pinch\Attribute\Contract]` attribute MUST NOT have their public API
      changed without explicit approval
- [ ] Component-specific exceptions MUST be placed in the component's `Exception/` subdirectory
- [ ] Event classes MUST follow the `{Action}{State}` naming pattern without "Event" suffix

### Testing Requirements

- [ ] New and changed functionality MUST be covered with comprehensive and meaningful PHPUnit 12 unit tests
- [ ] Tests coverage includes both "happy path" and "sad path" cases, including `null` and edge cases
- [ ] No self-fulfilling tests (e.g., testing a function that only returns true) or mocking the SUT
- [ ] Tests SHOULD demonstrate usage and expected behavior, not just check for exceptions
- [ ] PHPUnit MUST pass without errors or warnings for the entire codebase

### Code Quality, Type Safety & Static Analysis

- [ ] No use of `@phpstan-ignore` or `@phpstan-ignore-next-line` annotations without explicit approval
- [ ] Generic types should be used where appropriate (e.g., `Collection<T>`, `Result<T>`)
- [ ] Proper use of PHP 8.4 type features (union types, intersection types, property hooks)
- [ ] All methods MUST have proper parameter and return type declarations
- [ ] No use of `mixed` type unless absolutely necessary
- [ ] Code follows existing patterns in neighboring files and project conventions
- [ ] PHP Syntax Linting MUST pass without errors on the entire codebase
- [ ] PHPStan MUST pass without errors on the entire codebase
- [ ] PHP CodeSniffer MUST pass without errors on the entire codebase
- [ ] Use appropriate framework attributes (`#[Contract]`, `#[Internal]`, etc.)

### Framework Compliance

- [ ] Follows PSR standards (with type-safety deviations)
- [ ] Uses framework patterns (Service Providers, Value Objects, etc.)
- [ ] Follows framework package structure, namespace structure, and class naming conventions
- [ ] Domain objects include OpenAPI schema definitions when appropriate
- [ ] Uses component-specific exceptions from `Exception/` subdirectories
- [ ] Implements appropriate "\*Aware" interfaces when containing specific types

### Documentation

- [ ] Code should be "self-documenting" with clear method/variable/parameter names and meaningful "why vs what"
- [ ] When changing functionality, if there is existing documentation concerning the functionality in any package, it
      MUST be updated to reflect the changes
- [ ] If new functionality is added, it MUST be documented somewhere, probably in the appropriate package documentation;
      create if necessary. We can always move, edit or delete the documentation later, but it MUST be created
- [ ] OpenAPI schemas updated for new/changed domain objects

### Final Verification

- [ ] Check that all modified files follow framework conventions
- [ ] Ensure no debug code or temporary changes remain
- [ ] Verify proper use of `readonly` and `final` modifiers
- [ ] Confirm all helper functions are properly namespaced

**DO NOT mark a task as complete until ALL criteria are satisfied!**

## Quality Tools & Commands

### Running Quality Checks

The following commands MUST be run using the project Makefile to ensure proper Docker execution:

### Quality Check Priority Order

When fixing issues:

1. **Syntax Errors**: PHP parse errors
2. **Test Failures**: Broken functionality
3. **PHPStan Errors**: Type safety issues
4. **PHPCS Violations**: Style consistency
5. **Coverage Gaps**: Missing test cases

## Debugging & Development Tools

- XDebug for is available for step debugging
- PHPUnit 12 is used as the unit test framework
- Behat for behavior-driven development (BDD) tests (in template package)
- PHPStan for static analysis with custom rules. You may suggest new rules that help enforce our coding conventions
- PHP CodeSniffer with Custom Ruleset (`phoneburner/coding-standards`)
- Rector for code transformations
- Prettier for formatting (JavaScript, CSS, YAML, HTML, MD etc.)
- PsySH for interactive shell debugging (via `make shell` for custom REPL implementation)
- Monorepo Builder for managing the monorepo structure and dependencies
- GitHub Actions for CI/CD (automated testing, code quality checks, and deployment)
