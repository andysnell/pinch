# Pinch Framework Project Overview

## Project Overview

The Pinch Framework is a very highly opinionated PHP 8.4 API framework derived from PhoneBurner's original Salt
framework. It is designed and optimized to be deployed as a modern PHP backend for a REST API. The Pinch Framework is
not trying to be a general-purpose framework like Symfony or Laravel. The core philosophy of the framework includes
embracing best practices and standards; avoiding unnecessary complexity; providing robust functionality with minimum
cognitive overhead; and rapid application development.

IMPORTANT: This project is a monorepo for the Pinch Framework and not an application that uses the framework. The
`template` package is just a starting point and not a complete implementation. E.g., the default credentials in the
`.env.dist` file are intentionally insecure. It is safe to assume that an application based on this framework will
update those credentials to secure values as part of set up.

## Monorepo Organization

```
pinch/
├── packages/
│   ├── core/                    # Fundamental low-level utilities and abstractions
│   │   ├── src/                 # Source code for primitive components
│   │   │   ├── Array/           # Array manipulation utilities
│   │   │   ├── Attribute/       # Framework attributes (#[Contract], #[Internal], etc.)
│   │   │   ├── Enum/            # Enhanced enum functionality and functions
│   │   │   ├── Exception/       # Basic exception classes
│   │   │   ├── Filesystem/      # File I/O operations
│   │   │   ├── Iterator/        # Enhanced array and iteration support
│   │   │   ├── Math/            # Type-safe math functions
│   │   │   ├── Memory/          # Memory management utilities
│   │   │   ├── Random/          # Randomization utilities
│   │   │   ├── String/          # String manipulation and encoding
│   │   │   ├── Time/            # Comprehensive time handling
│   │   │   ├── Trait/           # Base traits
│   │   │   ├── Type/            # Type utilities and reflection
│   │   │   ├── Utility/         # General utilities
│   │   │   │── Uuid/            # UUID generation (ordered and random)
│   │   │   └── functions.php    # Global helper functions
│   │   └── tests/               # PHPUnit tests mirroring src structure
│   ├── component/               # Components with mostly "framework-agnostic" functionality
│   │   ├── src/                 # Source code (see detailed component tree below)
│   │   │   ├── App/             # Application lifecycle management
│   │   │   ├── Cache/           # PSR-6 & PSR-16 caching with tiered strategies and resource locking
│   │   │   ├── Collections/     # Enhanced collections (Maps, WeakSet)
│   │   │   ├── Configuration/   # Configuration management
│   │   │   ├── Container/       # Dependency injection system
│   │   │   ├── Cryptography/    # Complete crypto library (PASETO, encryption, hashing)
│   │   │   ├── Domain/          # Common domain value objects (Email, PhoneNumber, IpAddress)
│   │   │   ├── Http/            # PSR-7/15 HTTP implementations
│   │   │   ├── I18n/            # Internationalization (currencies, locales, regions)
│   │   │   ├── Logging/         # PSR-3 logging with buffering
│   │   │   ├── Mailer/          # Email composition and sending
│   │   │   └── MessageBus/      # Message Bus based communication
│   │   └── tests/               # PHPUnit tests mirroring src structure
│   ├── framework/               # Highly-opinionated framework implementations and configuration
│   │   ├── src/                 # source code
│   │   └── tests/               # test code
│   ├── phpstan/                 # Custom PHPStan rules
│   └── template/                # Application template
├── composer.json                # Root composer configuration
├── Makefile                     # Docker-based development commands
└── README.md                    # Main documentation
```

## Package Descriptions

### Core Package (`packages/core/`)

The most fundamental, low-level components providing essential utilities and abstractions. This package contains
primitive functions, basic types, utility classes, and common patterns used throughout the framework. Components include
Array, Attribute, Enum, Exception, Filesystem, Iterator, Math, Memory, Random, String, Time, Trait, Type, Utility, and
Uuid. These are framework-agnostic and have minimal dependencies.

### Components Package (`packages/component/`)

The higher-level components interfaces, service classes, and domain classes of the framework. We intend to keep this
package as framework-agnostic and third-party-dependency-agnostic as possible, so that it can be used alongside other
frameworks and ultimately used in the Salt framework. We only tie this package to the PSR standards and a few "golden"
packages like ramsey/uuid. This package depends on the `core` package for its fundamental utilities. There should be a
clear hierarchy of components in this package, with the most primitive (e.g. core) used by the more complex components.

### Framework Package (`packages/framework/`)

This is our "bridge" code that implements/extends the components package, providing the "batteries-included"
functionality. Unlike the `components` package, it depends on a number of third-party libraries like Symfony components.
For example, it provides a specific implementation of the `Phoneburner\Pinch\Component\Cache\Cache` interface, using
Redis as a driver, and a default configuration via a service provider. The implementing application can alter or
override this implementation by providing its own service provider definition.

### PHPStan Package (`packages/phpstan/`)

Custom PHPStan rules for the framework and implementations. This package provides static analysis rules that help ensure
type safety and adherence to framework conventions. As we develop the framework, we will be adding more rules to this
package to enforce our coding standards and best practices.

### Template Package (`packages/template/`)

An application template that serves as a starting point for new projects using the Pinch Framework. This package
provides a basic structure and configuration that can be extended for specific applications.

## Component Details

### Application Management

- **App/**: Bootstrap, Teardown, and Kernel execution events
- **Container/**: Service providers, parameter overrides, object containers
- **Configuration/**: Immutable configs with structured data

### Infrastructure Services

- **Cache/**: Multi-tier caching, PSR-6/16 support, append-only caches, locks, warmup strategies
- **Http/**: Complete HTTP stack with middleware, routing, sessions, cookies, CORS, HAL responses
- **Logging/**: PSR-3 logging with buffer support and trace functionality
- **MessageBus/**: Invokable handlers, message routing, event dispatching
- **Mailer/**: Email composition with attachments, priorities, and templating

### Domain Objects & Value Types

- **Domain/**:
    - Email addresses with validation
    - Phone numbers (E164 format, domestic/international)
    - IP addresses (v4/v6)
    - Memory units (binary/decimal)
- **Time/**: Clocks, durations, intervals, time zones, RFC standards
- **Uuid/**: Ordered UUIDs, random UUIDs, string wrappers
- **String/**: Manipulation, Base64/JSON encoding, binary strings
- **Math/**: Type-safe functions (int_floor, int_ceil, clamp, between)

### Security & Cryptography

- **Cryptography/**:
    - Asymmetric: Ed25519, X25519
    - Symmetric: AEGIS-256, XChaCha20-Blake2b, XChaCha20Poly1305, AES-GCM
    - PASETO: v1-v4 token implementations
    - Key Management: KeyId, derivation, storage
    - Hashing: HMAC, password hashing
    - `\Phoneburner\Pinch\Component\Cryptography\Natrium`: facade pattern implementation for all cryptography
      functionality

### Utilities & Helpers

- **Collections/**: Maps with array access, WeakSet
- **Iterator/**: Sorting, observability
- **Filesystem/**: Safe file operations
- **Serialization/**: PHP serializable support
- **Random/**: Weighted items, secure randomness
- **Type/**: Reflection helpers, casting functions
- **I18n/**: Currencies, locales, regions, subdivisions
- **Enum/**: Static methods, value extraction

## Namespace Conventions

- Top Level for Components: `PhoneBurner\Pinch\*`
- Core Package collects multiple components: `Phoneburner\Pinch\Component\Cache`, `Phoneburner\Pinch\Component\Http`,
  etc.
- Framework and PHPStan Package are independent components: `PhoneBurner\Pinch\Framework\*`
- Template Package is an application template, with its own project-specific namespaces, default is `App`
- Component namespaces represent cohesive units (e.g., `Database`, `Http`, `Cache`)
- Component namespaces in the framework package should shadow the component package namespaces, e.g.,
  `PhoneBurner\Pinch\Framework\Http` shadows `Phoneburner\Pinch\Component\Http` and provides specific implementations,
  configuration, and usage of classes from the component package
- Each component MAY have an `Exception/` subdirectory for component-specific exceptions
- Each component MAY have an `Attribute/` subdirectory for component-specific attributes
- Each component MAY have a `functions.php` file for component-specific pure, low-level functions
- Each component MAY have a `constants.php` file autoloading for constants that do not fit in a class definition
- Each framework component MAY have an `Event/` subdirectory for component-specific events
- Each framework component MAY have an `EventListener/` subdirectory for component-specific event listeners
- Each framework component MAY have a `{ComponentName}ServiceProvider.php` service provider
- Each framework component MAY have a `Config/` subdirectory for component-specific configuration files
- Each framework component MAY have a corresponding top-level `{framework-name}.php` file in the template `config/`
  directory, which should provide the default configuration for the component. For example, the `Cache` component
  would have a `config/cache.php` file in the template package, webhook configuration would in the `config/http.php`.

## Protected Files & Directories

DO NOT modify project-level configuration files without explicit permission. This includes, but is not limited to
`phpstan.neon`, `phpstan.dist.neon`, `phpunit.xml`, `phpunit.dist.xml`, `compose.yml`, `Dockerfile`, `rector.php`,
`phpcs.xml`, `.prettierignore`, `.gitignore`, `Makefile` `LICENSE`

## Future Expansion

We expect that additional packages will be added in the future. Specifically, we expect that namespaces currently in the
`components` with multiple dependencies on other components may be promoted to their own packages as they grow in
complexity and functionality. For example, the `Phoneburner\Pinch\Component\Http` or
`Phoneburner\Pinch\Component\Cryptography` namespaces will probably become their own packages at some point. This is the
reason why the `components` namespace is not named `PhoneBurner\Pinch\Components`, as it is intended to be a collection
of functional components that is not tied to the Pinch Framework specifically, but rather to the PhoneBurner PHP
ecosystem as a whole. We may also move service-specific functionality from the `framework` package to its own package
for things like Doctrine and Redis.
