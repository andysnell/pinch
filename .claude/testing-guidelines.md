# Testing Guidelines

## Test Development Tools

- XDebug for is available for step debugging and generating test coverage
- PHPUnit 12 is used as the unit test framework
- Paratest for parallel test execution
- Behat for behavior-driven development (BDD) tests (in template package)

## Test Guidelines

- Do not write unit tests classes for interfaces or traits, only test actual implementations
- Do not write tests that use reflection to private methods or properties. Test the public interface.
- Use the most specific/strict assertion possible (e.g., `assertSame` instead of `assertEquals`)
- The same quality standards and code conventions apply to tests as to the rest of the codebase
- When generating test coverage output, use `build/phpunit` as the output directory (for any format)
- No "self-fulfilling" tests (e.g., testing a function that only returns true) or mocking the SUT to make tests pass
- Test code, incuding fixtures, MUST follow the same conventions as non-test code (e.g., use proper imports, follow the same naming conventions)

## PHPUnit Configuration

- Unit tests use PHPUnit 12 with attributes (not annotations)
- Data providers MUST be static methods
- Test files for classes follow the pattern: `{ClassName}Test.php`
- Test files for functions follow the pattern: `{BaseDirectoryName}FunctionsTest.php`
- Tests mirror the source structure in `tests/` directory
- Use `#[Test]` attribute instead of `test*` method names
- Use `#[DataProvider('providerName')]` for parameterized tests
- Use test fixture classes for complex test data setup and mocking
- Create fixture classes in `tests/Fixtures/` or `tests/{Component}/Fixtures/` directories, instead of in the test class
- Reuse existing fixtures when possible to avoid duplication, but create new ones when necessary
- Mirror the component structure (e.g., `tests/EventSourcing/Fixtures/MockAggregateRoot.php`)

- Test method names should be descriptive and in camelCase
- Tests that create files or directories MUST clean up after themselves in the `tearDown()` method
- Tests SHOULD NOT leak, alter or affect global state (e.g., no `global` variables, superglobals, or static properties)

## Test Organization Best Practices

- Use the Arrange-Act-Assert pattern
- Use descriptive test names
- Avoid complex setup in tests, use test class helper methods, test class setup/teardown, and fixture classes when necessary
- Mock service classes and interfaces where appropriate; but prefer to use real value object instances
- Include both "happy path" and "sad path" test cases

## Testing Requirements (Acceptance Criteria)

- [ ] New and changed functionality MUST be covered with comprehensive and meaningful unit tests
- [ ] New and changed tests MUST follow all of the the [Test Guidelines](#test-guidelines) guidelines
- [ ] Tests coverage MUST include both "happy path" and "sad path" cases, including the `null` case (if applicable) and edge cases
- [ ] Tests coverage SHOULD demonstrate usage and expected behavior, not just check for exceptions or state
- [ ] Tests MUST NOT generate any unintended files or have persistent side effects
- [ ] PHPUnit MUST pass without errors or warnings for the entire codebase

## Running Tests

```bash
# Run all tests
make test

# Run specific PHPUnit package suite (adapt for single file tests)
docker compose run --rm php php vendor/bin/phpunit --testsuite=component

# Enable XDebug Code Coverage (can adapt with paratest or other output)
docker compose run --rm -e XDEBUG_MODE=coverage php php vendor/bin/phpunit --coverage-html coverage
```

## Testing Patterns

### Example Test Structure

```php
#[Test]
public function constructorThrowsExceptionWhenEmailAddressIsInvalid(): void
{
    $this->expectException(\UnexpectedValueException::class);
    $thsis->expectExceptionMessage('Invalid email format');
    new EmailAddress('invalid-email');
}

#[Test]
#[DataProvider('provide_valid_emails')]
public function constructorAcceptsValidEmailAddressValues(string $email): void
{
    $email_address = new EmailAddress($email);
    self::assertSame($email, $email_address->toString());
}

public static function provideValidEmails(): \Generator
{
    yield 'simple' => ['user@example.com'];
    yield 'subdomain' => ['user@mail.example.com'];
    yield 'plus addressing' => ['user+tag@example.com'];
}
```

### Framework-Specific Testing

#### Service Classes

- Test service registration in service providers
- Mock dependencies but use real value objects
- Test event emission for operations
- Verify proper dependency injection

#### Value Objects

- Test validation in constructors
- Test immutability
- Test Stringable implementation
- Test edge cases and null handling

#### Cross-Package Testing

- Changes in `core` → test `component`, `framework` and `template`
- Changes in `component` → test `framework` and `template`
- Changes in `framework` → test `template`
- New PHPStan rules → test all packages

## Important Notes

- If the prompt is requesting the addition of unit test coverage, do not change any non-test files
- Tests should be "self-documenting" with clear method/variable/parameter names
- Test fixtures should be reusable and follow the same coding standards as production code
- Coverage should aim for 100% but prioritize meaningful tests over quantity
- Test demonstrates usage and expected behavior, not just check for exceptions
