# Implementation Patterns

This document provides detailed patterns and examples for implementing Pinch Framework components.

## Implementation Approach

1. Read the project [coding standards](../.claude/coding-standards.md) and follow the conventions.
2. Analyze requirements and determine the correct package (core/components/framework)
3. Design namespace following Pinch hierarchy (PhoneBurner\Pinch\{Package}\*)
4. Implement with Pinch's strict type system (no mixed, mandatory declarations)
5. Add validation in constructors for value objects, use component exceptions
6. Write PHPUnit 12 tests with attributes and static data providers
7. Create OpenAPI schemas for domain objects, update README.md
8. Register services in appropriate service providers

## Value Object Implementation

Value objects are a core pattern in Pinch Framework:

```php
<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\ExampleDomain;

final readonly class EmailAddress implements \Stringable
{
    private string $value;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid email address: %s', $email)
            );
        }

        $this->value = $email;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(EmailAddress $other): bool
    {
        return $this->value === $other->value;
    }
}
```

## Service Implementation

Services follow different patterns than value objects:

```php
<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cache;

use PhoneBurner\Pinch\Component\Cache\Cache;
use PhoneBurner\Pinch\Component\Cache\CacheKey;

class RedisCache implements Cache
{
    /**
     * NOT final, but may have readonly properties
     */
    public function __construct(
        private readonly RedisAdapter $redis,
        private readonly int $default_ttl = 3600
    ) {
        // be proactive in validation (or better yet, compose with value objects)
        if ($this->default_ttl < 1) {
            throw new \UnexpectedValueException('Default TTL must be greater than zero');
        }
    }

    public function get(CacheKey $key): mixed
    {
        $value = $this->redis->get($key->toString());
        if ($value === false) {
            return null;
        }

        return \unserialize($value);
    }

    public function set(CacheKey $key, mixed $value, int|null $ttl = null): bool
    {
        return $this->redis->setex(
            $key->toString(),
            $ttl ?? $this->default_ttl,
            \serialize($value)
        );
    }
}
```

## Service Provider Implementation

```php
declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cache;

use PhoneBurner\Pinch\Component\App\ServiceProvider;
use PhoneBurner\Pinch\Component\Cache\Cache;

final class CacheServiceProvider implements ServiceProvider
{
    public function bind(): array
    {
        return [
            Cache::class => RedisCache::class,
        ];
    }

    public function register(App $app): void
    {
        $app->set(
        RedisAdapter::class,
        static function fn(App $app): RedisAdapter =>  new RedisAdapter(
                host: $app->config->host,
                port: $app->config->port,
                password: $app->config->password
            );
        });
    }
}
```

## Configuration Implementation

```php
declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cache;

use PhoneBurner\Pinch\Component\Configuration\ConfigStruct;

final readonly class CacheConfig extends ConfigStruct
{
    public function __construct(
        public string $host = 'localhost',
        public int $port = 6379,
        public string|null $password = null,
        public int $default_ttl = 3600,
        public string $prefix = 'pinch'
    ) {}
}
```

## Event Implementation

```php
declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Event;

// Note: No "Event" suffix
final readonly class CacheWriteStart
{
    public function __construct(
        public CacheKey $key,
        public mixed $value,
        public int $ttl
    ) {}
}

final readonly class CacheWriteCompleted
{
    public function __construct(
        public CacheKey $key,
        public bool $success
    ) {}
}

final readonly class CacheWriteFailed
{
    public function __construct(
        public CacheKey $key,
        public \Throwable $exception
    ) {}
}
```

## Exception Implementation

```php
declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Exception;

// Component-specific exceptions in Exception/ subdirectory
class CacheException extends \RuntimeException
{
    public static function connectionFailed(string $host, int $port): self
    {
        return new self(sprintf(
            'Failed to connect to cache server at %s:%d',
            $host,
            $port
        ));
    }
}

class CacheKeyException extends \InvalidArgumentException
{
    public static function invalidFormat(string $key): self
    {
        return new self(sprintf(
            'Cache key contains invalid characters: %s',
            $key
        ));
    }
}
```

## Middleware Implementation

```php
declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Note: No "Middleware" suffix
final class RateLimiter implements MiddlewareInterface
{
    public function __construct(
        private readonly RateLimitService $rate_limiter,
        private readonly int $requests_per_minute = 60
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $client_id = $this->extractClientId($request);

        if (!$this->rate_limiter->allowRequest($client_id, $this->requests_per_minute)) {
            throw new TooManyRequestsException();
        }

        return $handler->handle($request);
    }
}
```

## Command Implementation

```php
declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Note: Required "Command" suffix
#[AsCommand(
    name: 'user:create',
    description: 'Create a new user'
)]
final class UserCreateCommand extends Command
{
    public function __construct(
        private readonly UserService $user_service
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Thin command delegating to service
        $user = $this->user_service->create(
            email: new EmailAddress($input->getArgument('email')),
            phone: new PhoneNumber($input->getArgument('phone'))
        );

        $output->writeln(sprintf('User created: %s', $user->id->toString()));

        return Command::SUCCESS;
    }
}
```

## Package Placement Rules

### Core Package

- Fundamental utilities without framework dependencies
- No service classes, only functions and basic types
- Examples: Array helpers, String manipulation, Time constants

### Component Package

- Framework-agnostic interfaces and implementations
- Service interfaces and value objects
- May depend on PSR interfaces and "golden" packages
- Examples: Cache interface, EmailAddress value object

### Framework Package

- Opinionated implementations with third-party dependencies
- Service providers and concrete implementations
- Configuration classes and integrations
- Examples: RedisCache, SymfonyMailerAdapter

### Template Package

- Application-specific implementations
- Controllers, commands, and application services
- Project-specific configuration
- Examples: UserController, UserCreateCommand
