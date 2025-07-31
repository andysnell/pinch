# Coding Standards & Conventions

## Key Differences from Typical PHP Framework Projects

- IMPORTANT: PHP variables, properties, and parameters MUST be snake_case, not camelCase or PascalCase.
- E.164 format for phone numbers when cast to string ("+13145551234" not "3145551234")
- Configuration driven by environment variables and type-safe `\PhoneBurner\Pinch\Component\Configuration\ConfigStruct` objects composed together and in 'packages/template/config/*.php' files
- Wrapped third-party libraries to allow easy swapping
- Strict separation of service and domain concerns
- Heavy use of value objects instead of scalars
- PASETO tokens instead of JWT for API authentication
- HAL-JSON for API responses
- Extensive use of PHP 8.4 features (property hooks, asymmetric visibility)

## General Principles and Standards for PHP Code

- Strict separation of framework-agnostic, framework, and application concerns
- Strict separation of service and domain (e.g. value object) classes
- Follow PSR standards and use common design patterns
- Type safety is paramount: no `mixed` types, no implicit type conversions
- Favor explicitness over magic: no hidden/implicit dependencies, behavior, or side effects
- Use value objects and enums for domain types, ensuring immutability and type safety
- Avoid passing scalars between code boundaries; use value objects instead
- Prefer lightweight struct-like objects over shaped arrays for structured data
- Avoid global state, pass information between services using dependency injection
- Pass information between services explicitly, with the event dispatcher or the message bus
- Favor composition over inheritance but limit trait usage to avoid complexity
- Traits **MUST** have `@phpstan-require-implements` or `@phpstan-require-extends` annotation
- Some classes have a `Contract` attribute, which indicates that they are part of the public API and should not be changed without careful consideration. These classes are considered stable and should not be modified without following the request acceptance criteria
- Throw the most appropriate exception for the situation, e.g., `\InvalidArgumentException` for invalid argument **types** and `\UnexpectedValueException` for invalid argument **values**. Consider whether an error is a "logic" or "runtime" error, and use the appropriate exception family. New exceptions can be created if necessary, but should always (eventually) extend from the base `\LogicException` and `\RuntimeException` classes
- Avoid silencing errors with the `@` operator or suppressing exceptions with an empty `catch` block. Use proper error handling and the PSR-3 logger instead
- For a given operation, we should add reasonable event hooks, dispatched to the event dispatcher, even if we do not have an immediate need for a listener class. Follow the start/completed/failed pattern for events, e.g., `UserCreationStart`, `UserCreationCompleted`, and `UserCreationFailed`. This allows us to add listeners later without changing the code. Note that events are not suffixed with "Event"
- Controllers, console commands, listeners, and message handlers should be "thin" and delegate most of the work to service classes. They should only handle request/response, input/output, and basic validation
- Use attributes to express metadata, such as `#[Contract]` for public API classes, `#[MessageHandler]` for message handlers, and `#[ServiceProvider]` for service providers. Attributes should be used instead of annotations
- Consider that there will be several layers of abstraction in the framework, and that there is a hierarchy of functions and classes
- The more "low-level" or "primitive" the function or public class method, the more important performance is. For example, a pure function operating on and returning scalar/array values might be called frequently in a loop. Optimize these for performance, consider the generated opcodes and PHP compiler optimizations. If there are multiple good implementations, benchmark performance before choosing one
- Third-party library functionality should be wrapped, so that we can easily swap out the implementation with a different library -- end users should never have to directly import a class from a third-party library (except for the libraries used by the core package like `ramsey/uuid`)

## PHP Coding Standards

- **ALWAYS** use `declare(strict_types=1);` at the top of every PHP file
- Make full use of PHP 8.4 features: typed properties, union types, match expressions, readonly properties, property hooks
- Prefer type-safe wrappers (e.g., `int_floor()` over `floor()`)
- Parameter and Return types are mandatory for all methods
- Use named arguments for clarity when appropriate
- Validate coding style with PHP CodeSniffer, using the custom ruleset
- IMPORTANT: Variables, class properties, function/method parameters, etc. MUST be named in snake_case format, unless shadowing a parameter from a parent class or interface, in which case the variable MUST follow the parent class or interface's naming convention
- IMPORTANT: property hooks may need to be wrapped with `// phpcs:disable` and `// phpcs:enable` or `// phpcs:ignore` comments to avoid false positives from PHP CodeSniffer. This is a known limitation of the tool, and you are specifically allowed to use these comments to disable checks for specific lines or blocks of code, but only in this particular case
- Do not import short fully qualified class names from the global namespace, use `\DateTimeImmutable` or `\array_map()` instead of `DateTimeImmutable` or `array_map()` with imports
- Prefer `final readonly class` for immutable value objects
- Use `readonly` properties extensively for immutability

## Class Naming Conventions

- Class Naming: the `Null` and `Nullable` prefixes are not equivalent. The former is the bottom-type, the latter the top-type for the given interface. See `\Phoneburner\Pinch\Component\PhoneNumber\NullablePhoneNumber`, `\Phoneburner\Pinch\Component\PhoneNumber\PhoneNumber`, and `\Phoneburner\Pinch\Component\PhoneNumber\NullPhoneNumber`. Note that this does not apply to the `\PhoneBurner\Pinch\Type\Cast\NullableCast` helper
- Do not suffix interfaces, classes, and traits with `Interface`, `Class`, or `Trait`. Use descriptive names instead
- Do not suffix PSR-15 middleware classes with `Middleware` or `Handler`. Use descriptive names that indicate their purpose
- Console commands MUST be suffixed with `Command` (e.g., `UserCreateCommand`)
- Classes that create things may be suffixed with `Factory` (e.g., `UserFactory`)
- Classes that adapt a third-party service or implement a specific interface SHOULD be suffixed with `Adapter` (e.g., `CacheAdapter`, `SymfonyLockFactoryAdapter`)
- Event classes follow the pattern `{Action}{State}` without "Event" suffix (e.g., `UserCreationStart`, `UserCreationCompleted`, `UserCreationFailed`)
- Interfaces that indicate object capabilities use `*Aware` suffix (e.g., `EmailAddressAware`)

## Special Attributes

- **`#[Contract]`**: Marks classes/interfaces as public API with stability guarantees
- **`#[StableClassName]`**: FQCN stability for serialization/external references
- **`#[Internal]`**: Framework-internal usage only

## Service vs Domain Classes

### Service Classes:

- Will usually implement a framework or PSR interface, and provide functionality (e.g., `Phoneburner\Pinch\Component\Http\HttpClient`)
- Service classes "do things"; they consume other services and value objects
- Service classes MUST be registered in the service container via a service provider
- Service classes MUST NOT be marked `final` (but may have final methods)
- Service classes MUST NOT be marked `readonly` (but may have readonly properties)

### Domain Classes:

- Primarily Value Objects, but also may include entities and aggregates
- Represent domain concepts, encapsulate business logic (e.g., `PhoneBurner\Pinch\Domain\User`)
- Domain classes are typically immutable and represent a specific state or concept in the application
- Often use `final readonly class` for full immutability
- Include validation in constructors/factory methods
- Extensive use of value objects (e.g., `PhoneNumber`, `EmailAddress`)
- Immutable by default (use `readonly` properties)
- Type-safe operations via dedicated methods
- Implement `\Stringable` when appropriate
- Include validation logic in constructors
- Provide factory methods for complex creation

## Value Objects & Domain Types

Value objects are a core concept in the framework. They:

- Are immutable
- Represent a single value or concept
- Include validation in their constructor
- Are comparable by value, not identity
- Often implement `\Stringable` for convenient output
- Use type-safe operations instead of exposing primitives

## Helper Functions

- Organized by component (Math, String, Time, etc.)
- Located in`packages/core/src/{Component}/functions.php` and `packages/component/src/{Component}/functions.php`
- Always include proper use statements: `use function PhoneBurner\Pinch\{Component}\{function};`
- Global functions in `packages/core/src/functions.php`:
    - `proxy()`: Lazy proxy object creation
    - `ghost()`: Lazy ghost object initialization
    - `nullify()`: Convert false to null
    - `retry()`: Retry mechanism with exponential backoff
    - `compose()`: Function composition
    - `tap()`: Side-effect operations
    - `once()`: Memoization wrapper

## Exception Handling

- Each component has dedicated exceptions in `Exception/` subdirectory
- Extend from `\LogicException` for programming errors
- Extend from `\RuntimeException` for runtime errors
- Use `\InvalidArgumentException` for invalid argument types
- Use `\UnexpectedValueException` for invalid argument values
- Create component-specific exceptions when needed

## Generic Types & PHPStan

- Use PHPStan generics extensively: `@template`, `@param`, `@return`
- Type-safe collections: `Collection<T>`, `Map<TKey, TValue>`
- Conditional return types with `@phpstan-assert`
- Use `@phpstan-require-implements` and `@phpstan-require-extends` on traits
- Leverage PHPStan for compile-time type checking

## Service Providers

- Each component MAY have a Service Provider
- Service providers implement `ServiceProvider` interface
- `bind()` method returns interface-to-implementation mappings
- `register()` method handles service registration
- Register in `ContainerFactory::FRAMEWORK_PROVIDERS` if framework-level
- Use dependency injection extensively

## Configuration

- Configuration files return arrays with single, top-level key
- File naming: kebab-case (e.g., `message-bus.php`)
- Array key: snake_case (e.g., `'message_bus'`)
- Environment variables override configuration
- Configuration objects extend `ConfigStruct` for type safety

## Documentation

- Use PHPDoc only when it adds value beyond type declarations
- Prefer descriptive method/variable names over comments
- Document complex algorithms or business logic inline
- Domain objects should include companion `.yaml` files with OpenAPI schemas
- Component functionality MUST be documented in a component level `README.md`
- If a component has a `README.md` file, it MUST be updated to reflect any changes made to the component, including new functionality, changes to existing functionality, or changes to the component's structure. If no `README.md` file exists, it MUST be created to document the component's functionality, structure, and usage. Example code snippets must be included in the documentation to demonstrate usage and expected behavior, and MUST follow the same coding style and conventions as the rest of the codebase. The documentation MUST be clear, concise, and easy to understand.

## Code Quality Requirements

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

## Security Considerations

### Type Safety for Security

- Use value objects for input validation (Email, PhoneNumber, IpAddress)
- No `mixed` types in security-critical code
- Leverage readonly properties for immutable security data
- Implement proper error handling without information disclosure

### PASETO Authentication

- Use PASETO v4 (recommended) over JWT
- Configure proper token expiration and validation
- Integrate with middleware stack
- Handle token refresh and revocation

### API Security

- HAL-JSON responses with secure `_links` and `_embedded` structures
- RFC 9421 Message Signatures for webhooks
- Proper CORS configuration
- Rate limiting with Redis

### Cryptography

- Use `\Phoneburner\Pinch\Component\Cryptography\Natrium` facade
- Algorithm selection: AEGIS-256 for speed, XChaCha20-Blake2b for flexibility
- Proper key management with KeyId
- Argon2id for password hashing

## Framework Patterns

### Event Patterns

- Follow Start/Completed/Failed pattern without "Event" suffix
- Examples: `UserCreationStart`, `UserCreationCompleted`, `UserCreationFailed`
- Emit events even without immediate listeners for extensibility
- Events enable monitoring and future feature additions

### Middleware Patterns

- PSR-15 compliant without Middleware/Handler suffix
- Descriptive names indicating purpose
- Thin middleware delegating to services
- Proper ordering in middleware stack

### Performance Patterns

- Optimize low-level functions in core package
- Use type-safe wrappers for better opcodes
- Implement lazy loading with `proxy()` and `ghost()`
- Cache validation results in value objects when appropriate
- Consider PHP compiler optimizations for frequently called code

## Common Pitfalls to Avoid

- IMPORTANT: Don't assume libraries are available, even if defined in the root composer.json. Check the composer.json of the package you are working on. Remember that this is a monorepo, and each package has its own dependencies, and will be installed separately
- IMPORTANT: Do not "fix" test/quality errors by ignoring them or changing configuration files without explicit permission. Do not use `@phpstan-ignore` or `@phpstan-ignore-next-line` annotations without explicit approval
- Check for component-specific exceptions before creating new ones
- `function.php` and `constants.php` files must be registered in the package level and root `composer.json` files for file autoloading
- Never mark service classes as `final` or `readonly`
- Always check for existing patterns in neighboring files before implementing
- Don't create circular dependencies between packages
- Ensure components package remains framework-agnostic
