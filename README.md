<p align="center"><a href="https://github.com/phoneburner/pinch" target="_blank">
<img src="packages/template/public/images/pinch-logo.svg" width="350" alt="Logo"/>
</a></p>

# Pinch Framework

> Feels like home, just without the salty tears of frustration

The Pinch Framework is a "batteries-included," very-highly-opinionated PHP
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
  a significant upgrade from the previous version. Notably, unit tests are defined
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

## Docker

The default Docker image used by this project is based on Docker's official PHP
8.4 FPM image with default with a few additional extensions installed and configuration tweaks.
The base image's "php.ini-production" configure is used as the basis for the php.ini
file. The values in this file are overridden by the "php-development.ini" or
"php-production.ini" files, depending on the environment.

## Sodium

The official PHP 8.4 FPM image ships with an older version of libsodium, which does not support
the latest functionality added to PHP's Sodium extension. The project image updates
1.0.20, so AEGIS-256 related functions are available.

### XDebug

The PHP Docker image used by this project includes the XDebug extension, but it is
not enabled in the same way as the other extensions. To load the XDebug extension,
"zend_extension=xdebug.so" must be added to an INI file. It is enabled by default
in the Docker image as the "php-development.ini" is copied to the PHP INI directory
as "settings.ini". Production builds should copy the "php-production.ini" file
instead, which does not include the XDebug extension (enabling the JIT instead).

## Notes

- Events can be defined at the component level; Event listeners must be defined at the framework or application level.
