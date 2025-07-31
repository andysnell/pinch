<p align="center"><a href="https://github.com/phoneburner/pinch" target="_blank">
<img src="packages/template/public/images/pinch-logo.svg" width="350" alt="Logo"/>
</a></p>

# Pinch Framework

> Feels like home, just without the salty tears of frustration

The Pinch Framework is a "batteries-included", very-highly-opinionated PHP
framework, derived from the original Salt framework/application used by PhoneBurner.
While modeled on other modern "general purpose" frameworks like Symfony and Laravel,
the Pinch Framework is designed and optimized as an API backend.

Ideally, it adapts the best core features of Salt without dragging along unnecessary
complexity, technical debt, and the (many) design decisions we regret. The goal is
to provide users with a robust framework with minimum cognitive overhead from the original
Salt framework, avoiding the pitfalls of bringing in a full-fledged third-party
framework and trying to adapt that to our needs.

### Guiding Principles

1. Compatiblity with the PSRs should be the general rule, but sensible deviations are allowed, especially in the name of type safety.
2. Where practical, third-party library code should be wrapped in a way that lets us expose our own interface. This
   allows us to swap out the underlying library without changing application code.
3. Separation of "framework" and "application" concerns
4. Take the best parts of Salt, leave the rest, and add new features wrapping the best of modern PHP
5. Configuration driven, with environment variables as the primary source of overrides

### Notable Differences from Salt

- The time zone configuration for PHP and the database is set to UTC by default.
- Configuration is defined by the environment, and not by the path value of a request.
- Overriding configuration values is done via environment variables, not by adding local configuration files.
- Database migrations are handled by
  the [Doctrine Migrations](https://www.doctrine-project.org/projects/migrations.html) library, as opposed to Phinx.
- PHPUnit 12 is used for testing, this is
  a significant upgrade from the previous version. Notably unit tests are defined
  with attributes and data providers must be defined as static functions.
- When cast to a string, `\Phoneburner\Pinch\Component\PhoneNumber\DomesticPhoneNumber` is formatted as an
  E.164 phone number ("+13145551234"), instead a ten-digit number ("3145551234").

### Backwards Capability Guarantees

Classes and interfaces with the `#[PhoneBurner\Pinch\Attribute\Usage\Contract]` attribute
are considered part of the public API of the framework and should not be changed without
a major version bump. These "contracts" can be freely used in application code.

Conversely, classes and interfaces with the `#[PhoneBurner\Pinch\Attribute\Usage\Internal]`
attribute are very tightly coupled to third-party vendor and/or framework logic,
and should not be used in application code.

### Included Functionality

- PSR-7/PSR-15 Request & Response Handling
- PSR-11 Dependency Injection Container
- PSR-3 Logging with Monolog
- PSR-14 Event Dispatching based on Symfony EventDispatcher
- Local/Remote Filesystem Operations with Flysystem
- Development Environment Error Handling with Whoops
- Console Commands with Symfony Console
- Interactive PsySH Shell with Application Runtime
- Doctrine ORM & Migrations
- Redis for Remote Caching with PSR-6/PSR-16 Support
- RabbitMQ for Message Queues and Job Processing
- Task Scheduling with Cron Expression Parsing with Symfony Scheduler
- SMTP/API Email Sending with Symfony Mailer

### Conventions

- Component Namespaces like `PhoneBurner\Pinch\Framework\Database` should represent
  a cohesive "component".
- Each Component namespace MAY have a Service Provider class, which is responsible for
  registering related services for that component and any subcomponents with the DI container.
  Non-optional framework level service providers MUST be listed in the
  `\PhoneBurner\Pinch\Framework\Container\ContainerFactory::FRAMEWORK_PROVIDERS` array.
- Each Component namespace MAY have a configuration file, the name of which should be
  component in kabob-case, e.g. `database.php` or `message-bus.php` This file should
  return an array of configuration values, with a single top-level key. That key
  MUST be the component name in snake case, e.g. `'database'` or `'message_bus'`.

## Helper Functions

### Math

The Math helper functions provide type-safe mathematical operations and utilities for common calculations.

#### `int_floor(int|float $number): int`

Rounds a number down to the next lowest integer and returns it as an integer type. This is a type-safe wrapper around PHP's `floor()` function which historically returns a float.

```php
use function PhoneBurner\Pinch\Math\int_floor;

echo int_floor(4.3);   // 4
echo int_floor(9.999); // 9
echo int_floor(-3.14); // -4
```

#### `int_ceil(int|float $number): int`

Rounds a number up to the next highest integer and returns it as an integer type. This is a type-safe wrapper around PHP's `ceil()` function which historically returns a float.

```php
use function PhoneBurner\Pinch\Math\int_ceil;

echo int_ceil(4.3);   // 5
echo int_ceil(9.001); // 10
echo int_ceil(-3.14); // -3
```

#### `clamp(int|float $value, int|float $min, int|float $max): int|float`

Constrains a value to be within a specified range. If the value is less than the minimum, returns the minimum. If greater than the maximum, returns the maximum. Otherwise returns the original value.

```php
use function PhoneBurner\Pinch\Math\clamp;

echo clamp(5, 1, 10);    // 5 (within range)
echo clamp(0, 1, 10);    // 1 (below minimum)
echo clamp(15, 1, 10);   // 10 (above maximum)
echo clamp(3.7, 1.5, 5.5); // 3.7 (works with floats)
```

#### `int_clamp(int|float $value, int $min, int $max): int`

Similar to `clamp()` but always returns an integer value. If the value is within range but is a float, it will be cast to an integer.

```php
use function PhoneBurner\Pinch\Math\int_clamp;

echo int_clamp(5.7, 1, 10);  // 5 (cast to int)
echo int_clamp(0, 1, 10);    // 1 (below minimum)
echo int_clamp(15, 1, 10);   // 10 (above maximum)
```

#### `between(int|float $value, int|float $min, int|float $max, IntervalBoundary $boundary = IntervalBoundary::ClosedClosed): bool`

Checks if a value falls within a specified range with configurable boundary inclusion. Uses PHP's `IntervalBoundary` enum to specify whether the boundaries are inclusive or exclusive.

```php
use function PhoneBurner\Pinch\Math\is_between;
use Random\IntervalBoundary;

// Default: Both boundaries inclusive [min, max]
echo is_between(5, 1, 10);  // true (5 is between 1 and 10, inclusive)
echo is_between(1, 1, 10);  // true (1 equals minimum)
echo is_between(10, 1, 10); // true (10 equals maximum)

// Open interval (min, max) - both boundaries exclusive
echo is_between(1, 1, 10, IntervalBoundary::OpenOpen);   // false (1 equals min)
echo is_between(5, 1, 10, IntervalBoundary::OpenOpen);   // true
echo is_between(10, 1, 10, IntervalBoundary::OpenOpen);  // false (10 equals max)

// Half-open intervals
echo is_between(1, 1, 10, IntervalBoundary::ClosedOpen); // true (includes min, excludes max)
echo is_between(10, 1, 10, IntervalBoundary::ClosedOpen); // false

echo is_between(1, 1, 10, IntervalBoundary::OpenClosed); // false (excludes min, includes max)
echo is_between(10, 1, 10, IntervalBoundary::OpenClosed); // true
```
