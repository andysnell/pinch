<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Type;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\Exception as CarbonException;
use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Time\Standards\AnsiSql;

function get_debug_value(mixed $value): string
{
    $type = \get_debug_type($value);
    return match ($type) {
        'null' => 'null',
        'bool' => $value ? '(bool)true' : '(bool)false',
        'int' => '(int)' . $value,
        'float' => '(float)' . $value,
        'string' => '(string)' . $value,
        'array' => \print_r($value, true),
        default => $type,
    };
}

/**
 * @phpstan-assert-if-true \Stringable|string $value
 */
function is_stringable(mixed $value): bool
{
    return \is_string($value) || $value instanceof \Stringable;
}

/**
 * Note that while our `cast_string()` and related functions will cast enums to
 * their value or name, depending on if the enum is backed, this function returns
 * false for enums, to be consistent with the native PHP cast behavior.
 *
 * @phpstan-assert-if-true \Stringable|scalar|null $value
 */
function is_castable_to_string(mixed $value): bool
{
    return \is_scalar($value) || $value === null || $value instanceof \Stringable;
}

/**
 * @phpstan-assert-if-true array|\ArrayAccess<mixed,mixed> $value
 */
function is_accessible(mixed $value): bool
{
    return \is_array($value) || $value instanceof \ArrayAccess;
}

/**
 * Returns true if passed an array, an instance of Arrayable or \Traversable.
 *
 * Note: This will return true for \Traversable instances that have keys
 * that are not valid array keys.
 *
 * @phpstan-assert-if-true array<mixed, mixed>|\Traversable<mixed, mixed>|Arrayable<array-key, mixed> $value
 */
function is_arrayable(mixed $value): bool
{
    return \is_iterable($value) || $value instanceof Arrayable;
}

/**
 * @phpstan-assert-if-true class-string $value
 */
function is_class_string(mixed $value): bool
{
    return \is_string($value) && (\class_exists($value) || \interface_exists($value));
}

/**
 * @template T of object
 * @param class-string<T> $type
 * @phpstan-assert-if-true class-string<T> $value
 */
function is_class_string_of(string $type, mixed $value): bool
{
    return \is_string($value) && \is_a($value, $type, true);
}

/**
 * Returns true if the value is an object or a class-string
 *
 * @phpstan-assert-if-true object|class-string $value
 */
function is_class(mixed $value): bool
{
    return \is_object($value) || is_class_string($value);
}

/**
 * @phpstan-assert-if-true non-empty-array $value
 */
function is_nonempty_array(mixed $value): bool
{
    return \is_array($value) && $value !== [];
}

/**
 * @phpstan-assert-if-true non-empty-list $value
 */
function is_nonempty_list(mixed $value): bool
{
    return \is_array($value) && \array_is_list($value) && $value !== [];
}

/**
 * @phpstan-assert-if-true positive-int $value
 */
function is_positive_int(mixed $value): bool
{
    return \is_int($value) && $value > 0;
}

/**
 * @phpstan-assert-if-true negative-int $value
 */
function is_negative_int(mixed $value): bool
{
    return \is_int($value) && $value < 0;
}

/**
 * @phpstan-assert-if-true non-positive-int $value
 */
function is_non_positive_int(mixed $value): bool
{
    return \is_int($value) && $value <= 0;
}

/**
 * @phpstan-assert-if-true non-negative-int $value
 */
function is_non_negative_int(mixed $value): bool
{
    return \is_int($value) && $value >= 0;
}

/**
 * @phpstan-assert-if-true int<min,-1> $value
 */
function is_non_zero_int(mixed $value): bool
{
    return \is_int($value) && $value !== 0;
}

/**
 * @phpstan-assert-if-true resource $value
 */
function is_stream_resource(mixed $value): bool
{
    return \get_debug_type($value) === 'resource (stream)';
}

/**
 * @phpstan-assert-if-true non-empty-string $value
 */
function is_nonempty_string(mixed $value): bool
{
    return \is_string($value) && $value !== '';
}

/**
 * Type Narrowing Functions
 *
 * These functions are used to ensure that the value passed to them is of the
 * expected type. If the value is not of the expected type, an exception is thrown.
 * Otherwise, the original value is returned. This is useful for "inlining" the
 * type assertion. The functions are declared in a way that helps static analysis
 * tools like PHPStan to understand the expected types.
 *
 * These functions throw a \InvalidArgumentException (a logic exception, indicating
 * a bug in the code rather than a user error) when the value is not of the expected
 * type. The expectation is that these are used in places where we are already
 * confident that the value should be of the expected type and should have type
 * safety checks in place.
 *
 * NOTE: the difference between `narrow_*` and `cast_*` functions is that
 * the former will throw an exception if the value is not of the expected type,
 * and the latter will attempt to cast the value into the expected type. That is,
 * the `narrow_*` functions are primarily used for asserting the type of the value,
 * while cast functions are used for converting a value from one type to another.
 */

/**
 * Narrow the type of the value argument to an object of a given type.
 *
 * @template T of object
 * @param class-string<T> $type
 * @return T
 * @phpstan-assert T $value
 */
function narrow(string $type, mixed $value): object
{
    return $value instanceof $type ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an instance of %s, but got %s', $type, \get_debug_type($value)),
    );
}

/***
 * @template T of object
 * @param class-string<T> $type
 * @return null|T
 * @phpstan-assert null|T $value
 */
function narrow_nullable(string $type, mixed $value): object|null
{
    if ($value === null) {
        return null;
    }

    return $value instanceof $type ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an instance of %s, but got %s', $type, \get_debug_type($value)),
    );
}

/**
 * Narrows the type of the value argument to a class-string of a given type.
 * Optionally, the passed type argument can be null, in which case it will only
 * narrow to a class-string without checking the type.
 *
 * @template T of object
 * @param class-string<T>|null $type
 * @phpstan-assert-if-true ($type is null ? class-string : class-string<T>) $value
 * @return ($type is null ? class-string : class-string<T>)
 */
function narrow_class_string(string|null $type, mixed $value): string
{
    if ($type === null) {
        return is_class_string($value) ? $value : throw new \InvalidArgumentException(
            \sprintf('Expected a class-string, but got %s', get_debug_value($value)),
        );
    }

    return is_class_string_of($type, $value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a class-string of %s, but got %s', $type, get_debug_value($value)),
    );
}

/**
 * @template T of object
 * @param class-string<T>|null $type
 * @phpstan-assert-if-true ($type is null ? class-string|null : class-string<T>|null) $value
 * @return ($type is null ? class-string|null : class-string<T>|null)
 */
function narrow_nullable_class_string(string|null $type, mixed $value): string|null
{
    if ($value === null) {
        return null;
    }

    if ($type === null) {
        return is_class_string($value) ? $value : throw new \InvalidArgumentException(
            \sprintf('Expected a class-string, but got %s', get_debug_value($value)),
        );
    }

    return is_class_string_of($type, $value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a class-string of %s, but got %s', $type, get_debug_value($value)),
    );
}

/**
 * @phpstan-assert string $value
 */
function narrow_string(mixed $value): string
{
    return \is_string($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a string, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert string|null $value
 */
function narrow_nullable_string(mixed $value): string|null
{
    if ($value === null) {
        return null;
    }

    return \is_string($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a string, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert non-empty-string $value
 * @return non-empty-string
 */
function narrow_nonempty_string(mixed $value): string
{
    return (\is_string($value) && $value !== '') ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a non-empty string, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert non-empty-string|null $value
 * @return non-empty-string|null
 */
function narrow_nullable_nonempty_string(mixed $value): string|null
{
    if ($value === null) {
        return null;
    }

    return (\is_string($value) && $value !== '') ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a non-empty string, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert int $value
 */
function narrow_int(mixed $value): int
{
    return \is_int($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an int, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert int|null $value
 */
function narrow_nullable_int(mixed $value): int|null
{
    if ($value === null) {
        return null;
    }

    return \is_int($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an int, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert positive-int $value
 * @return positive-int
 */
function narrow_positive_int(mixed $value): int
{
    return is_positive_int($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a positive int, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert positive-int|null $value
 * @return positive-int|null
 */
function narrow_nullable_positive_int(mixed $value): int|null
{
    if ($value === null) {
        return null;
    }

    return is_positive_int($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a positive int, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert float $value
 */
function narrow_float(mixed $value): float
{
    return \is_float($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a float, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert float|null $value
 */
function narrow_nullable_float(mixed $value): float|null
{
    if ($value === null) {
        return null;
    }

    return \is_float($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a float, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert bool $value
 */
function narrow_bool(mixed $value): bool
{
    return \is_bool($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a bool, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert bool|null $value
 */
function narrow_nullable_bool(mixed $value): bool|null
{
    if ($value === null) {
        return null;
    }

    return \is_bool($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a bool, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert array<array-key, mixed> $value
 * @return array<array-key, mixed>
 */
function narrow_array(mixed $value): array
{
    return \is_array($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an array, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert array<array-key, mixed>|null $value
 * @return array<array-key, mixed>|null
 */
function narrow_nullable_array(mixed $value): array|null
{
    if ($value === null) {
        return null;
    }

    return \is_array($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an array, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert iterable<mixed, mixed> $value
 * @return iterable<mixed, mixed>
 */
function narrow_iterable(mixed $value): iterable
{
    return \is_iterable($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an iterable, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert iterable<mixed, mixed>|null $value
 * @return iterable<mixed, mixed>|null
 */
function narrow_nullable_iterable(mixed $value): iterable|null
{
    if ($value === null) {
        return null;
    }

    return \is_iterable($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an iterable, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert array<array-key, mixed>|\ArrayAccess<mixed, mixed> $value
 * @return array<array-key, mixed>|\ArrayAccess<mixed, mixed>
 */
function narrow_accessible(mixed $value): \ArrayAccess|array
{
    return is_accessible($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an accessible type, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert array<array-key, mixed>|\ArrayAccess<mixed, mixed>|null $value
 * @return array<array-key, mixed>|\ArrayAccess<mixed, mixed>|null
 */
function narrow_nullable_accessible(mixed $value): \ArrayAccess|array|null
{
    if ($value === null) {
        return null;
    }

    return is_accessible($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected an accessible type, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert callable $value
 */
function narrow_callable(mixed $value): callable
{
    return \is_callable($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a callable, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert callable|null $value
 */
function narrow_nullable_callable(mixed $value): callable|null
{
    if ($value === null) {
        return null;
    }

    return \is_callable($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a callable, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert resource $value
 * @return resource
 */
function narrow_resource(mixed $value): mixed
{
    return \is_resource($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a resource, but got %s', get_debug_value($value)),
    );
}

/**
 * @phpstan-assert resource|null $value
 * @return resource|null
 */
function narrow_nullable_resource(mixed $value): mixed
{
    if ($value === null) {
        return null;
    }

    return \is_resource($value) ? $value : throw new \InvalidArgumentException(
        \sprintf('Expected a resource, but got %s', get_debug_value($value)),
    );
}

/**
 * PHP has some funky behavior with type juggling values into string, and this function
 * may deviate from the native PHP behavior, please see the casting notes below.
 *
 * Casting Notes:
 * - Warning: floats may lose precision when converted to strings, but 123.1234 is cast to "123.1234"
 * - The float 0.0 is cast to "0.0", not "0", as in the native PHP behavior, to be consistent with passing "O.0" as the value
 * - Otherwise, "integer" floats like 12.0 cast to "12", as they are internally type-juggled before cast to strings
 * - Special floats like \INF and \NAN are internally juggled and become "INF" and "NAN"
 * - The values true and false are internally juggled and become "1" and "", respectively.
 * - Backed enums return their value, non-backed enums return their name
 * - Anything not a string, scalar, or enum will throw an \InvalidArgumentException
 */
function cast_string(mixed $value): string
{
    return match (true) {
        $value === null => '',
        \is_string($value) => $value,
        \is_scalar($value) => $value === 0.0 ? '0.0' : (string)$value,
        $value instanceof \Stringable => (string)$value,
        $value instanceof \BackedEnum => (string)$value->value,
        $value instanceof \UnitEnum => $value->name,
        default => throw new \InvalidArgumentException('Invalid type for string cast: ' . \get_debug_type($value)),
    };
}

/**
 * Casting Notes:
 *  - null values will return null
 *  - boolean values will be cast to "1" or ""
 *  - See `cast_string()` for notes on casting other values
 *
 * @return ($value is null ? null : string)
 */
function cast_nullable_string(mixed $value): string|null
{
    return $value !== null ? cast_string($value) : null;
}

/**
 * IMPORTANT: This function only considers "" to be an empty string. The value
 * "0" is not considered an empty string, unlike the native PHP behavior. This is
 * intentional because in a type-safe application, the value "0" is likely to be
 * meaningful. For example, when parsing request parameters, there _should_ be
 * a difference between a parameter with the value "0" and one with "" or null.
 *
 * Casting Notes:
 *  - See `cast_nullable_string()` for notes on casting
 *  - The value `false` is cast null
 *
 * @return non-empty-string|null
 */
function cast_nullable_nonempty_string(mixed $value): string|null
{
    $value = cast_nullable_string($value);
    return $value !== '' ? $value : null;
}

/**
 * @return ($value is null ? null : bool)
 */
function cast_nullable_bool(mixed $value): bool|null
{
    return $value !== null ? (bool)$value : null;
}

/**
 * @return ($value is false|null ? null : true)
 */
function cast_nullable_nonempty_bool(mixed $value): bool|null
{
    return $value ? true : null;
}

/**
 * @return ($value is null ? null : int)
 */
function cast_nullable_int(mixed $value): int|null
{
    return match (true) {
        \is_int($value), $value === null => $value,
        \is_scalar($value) => (int)$value,
        $value instanceof \BackedEnum => (int)$value->value,
        default => throw new \InvalidArgumentException('Invalid type for integer cast: ' . \get_debug_type($value)),
    };
}

/**
 * @return int<min,-1>|int<1,max>|null
 */
function cast_nullable_nonempty_int(mixed $value): int|null
{
    return cast_nullable_int($value) ?: null;
}

/**
 * @return ($value is null ? null : float)
 */
function cast_nullable_float(mixed $value): float|null
{
    return match (true) {
        \is_float($value), $value === null => $value,
        \is_scalar($value) => (float)$value,
        $value instanceof \BackedEnum => (float)$value->value,
        default => throw new \InvalidArgumentException('Invalid type for float cast: ' . \get_debug_type($value)),
    };
}

function cast_nullable_nonempty_float(mixed $value): float|null
{
    return cast_nullable_float($value) ?: null;
}

/**
 * @phpstan-assert array<array-key, mixed>|null $value
 * @return non-empty-array<array-key, mixed>|null
 */
function cast_nullable_nonempty_array(mixed $value): array|null
{
    return narrow_nullable_array($value) ?: null;
}

function cast_nullable_datetime(mixed $value): CarbonImmutable|null
{
    try {
        return match (true) {
            $value instanceof CarbonImmutable, $value === null => $value,
            $value === AnsiSql::NULL_DATETIME, $value === '' => null,
            $value instanceof \DateTimeInterface, \is_string($value) => CarbonImmutable::make($value),
            \is_int($value) => CarbonImmutable::createFromTimestamp($value),
            default => throw new \InvalidArgumentException('Invalid type for datetime cast: ' . \get_debug_type($value)),
        };
    } catch (CarbonException) {
        return null;
    }
}
