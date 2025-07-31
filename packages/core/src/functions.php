<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch;

use PhoneBurner\Pinch\Type\Reflect;

/**
 * Use when you don't control the instantiation of the object, but have a factory
 * that can return an instance of the object, e.g. where you would normally not
 * (or cannot) just use "new" to create an instance.
 *
 * Note: this method wraps the passed factory in another closure that will make sure
 * that the object ultimately returned is not a lazy object. This is useful
 * when we do not necessarily know if the factory is going to give us something
 * that is lazy or not, e.g. If the factory is just resolving the class from
 * the PSR-11 container, for something like a database connection or entity,
 * the object returned by the container may be another ghost or proxy.
 *
 * @param callable(T): T $factory
 * @return T&object
 * @see Reflect::proxy() for a more complete implementation
 * @template T of object
 */
function proxy(callable $factory): object
{
    // We need to make sure that the factory is a \Closure that returns an instance
    // of the class that it is supposed to be creating. Using first class
    // callable syntax to should return the same instance if the
    // original initializer is already a \Closure and static.
    $factory = $factory(...);
    $initializer_reflection = new \ReflectionFunction($factory);
    \assert($initializer_reflection->getNumberOfParameters() === 1);

    /** @var class-string<T> $class */
    $class = (string)$initializer_reflection->getReturnType();
    \assert(\class_exists($class));

    $class_reflection = new \ReflectionClass($class);

    return $class_reflection->newLazyProxy(
        static fn(object $object): object => $class_reflection->initializeLazyObject($factory($object)),
    );
}

/**
 * Use when you control the instantiation of the object, e.g. where you would
 * normally use "new" to create an instance, passing in the object's dependencies.
 *
 * @see Reflect::ghost() for a more complete implementation
 * @template T of object
 * @param \Closure(T): void $initializer
 * @return T&object
 */
function ghost(\Closure $initializer): object
{
    // we need to make sure that the initializer is a \Closure that takes a single argument
    $initializer_reflection = new \ReflectionFunction($initializer);
    \assert($initializer_reflection->getNumberOfParameters() === 1);

    /** @var class-string<T> $class */
    $class = (string)$initializer_reflection->getParameters()[0]->getType();
    \assert(\class_exists($class));

    return new \ReflectionClass($class)->newLazyGhost($initializer);
}

/**
 * Returns null if the value is false, otherwise returns the value.
 *
 * @template T
 * @param T|false $value
 * @return ($value is false ? null : T)
 */
function nullify(mixed $value): mixed
{
    return $value === false ? null : $value;
}

/**
 * @template T
 * @param callable(): T $callback
 * @return T
 */
function retry(callable $callback, int $attempts = 5, int $delay_microseconds = 1000): mixed
{
    if ($attempts < 1) {
        throw new \UnexpectedValueException('max_attempts must be greater than 0');
    }

    if ($delay_microseconds < 0) {
        throw new \UnexpectedValueException('delay_microseconds must be greater than or equal to 0');
    }

    retry:
    try {
        return $callback();
    } catch (\Exception $e) {
        if (--$attempts) {
            \usleep($delay_microseconds);
            goto retry;
        }
    }

    throw $e;
}

function not(callable $callback): callable
{
    return static fn(...$args): bool => ! $callback(...$args);
}

/**
 * Creates a pipeline of functions where the output of one becomes the
 * input of the next (left-to-right)
 *
 * @example
 * $shout = compose(\trim(...), \strtoupper(...), fn($s) => $s . '!');
 * echo $shout(" hello "); // "HELLO!"
 */
function compose(callable ...$functions): callable
{
    return static fn(mixed $value): mixed => \array_reduce(
        $functions,
        static fn($carry, $fn): mixed => $fn($carry),
        $value,
    );
}

/**
 * @template T
 * @param T $value
 * @param callable(T): mixed $callback
 * @return T
 */
function tap(mixed $value, callable $callback): mixed
{
    $callback($value);
    return $value;
}

/**
 * Returns a callable that invokes the given function only once.
 *
 * On the first call, the provided function is executed and its return value
 * is cached. All subsequent calls to the returned callable will return
 * the same result without re-executing the original function.
 *
 * This is useful for lazy initialization, one-time setup, or deferred
 * computation where the result is stable and side-effect-free.
 *
 * @template T
 * @param callable(): T $fn The function to execute once.
 * @return callable(): T A callable that caches and returns the result of the first execution.
 * @example
 * $init = once(function () {
 *     echo "Initializing...\n";
 *     return random_int(1, 1000);
 * });
 *
 * echo $init(); // Executes and returns value
 * echo $init(); // Returns cached value
 */
function once(callable $fn): callable
{
    $called = false;
    $result = null;
    return static function () use (&$called, &$result, $fn): mixed {
        if ($called) {
            return $result;
        }

        $called = true;
        return $result = $fn();
    };
}

/**
 * If the $value argument is callable, call it with the parameters passed in
 * the $args array, otherwise, just pass through the value unchanged.
 */
function value(mixed $value, mixed ...$args): mixed
{
    return \is_callable($value) ? \call_user_func_array($value, $args) : $value;
}

/**
 * Provides a convenient way to produce a callback that calls a method on an
 * object that is passed to that callback. For example, when performing a
 * simple mapping operation over an iterable of objects, and just returning
 * the return value of a member method on those objects.
 *
 * Example 0:
 * Old: \array_map(fn(CarbonImmutable $datetime) => $datetime->getTimestamp(), $dates);
 * New: \array_map(Func::fwd('getTimestamp'), $dates);
 *
 * Example 1:
 * Old: \array_map(fn(CarbonImmutable $datetime) => $datetime->format('Y-m-d'), $dates);
 * New: \array_map(Func::fwd('getTimestamp', 'Y-m-d'), $dates);
 */
function func_fwd(string $method, mixed ...$args): \Closure
{
    return static fn(object $subject) => $subject->{$method}(...$args);
}

function noop(): \Closure
{
    static $noop = static fn(): null => null;
    return $noop;
}
