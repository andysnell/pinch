<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Array;

use PhoneBurner\Pinch\Iterator\Sort\Order;

use function PhoneBurner\Pinch\Type\is_arrayable;

/**
 * The PHP array_* functions only work with array primitives; however, it is
 * not uncommon to have a $variable that is known to have array-like behavior
 * but not know if it is an array or an instance of Traversable. This method
 * allows for clean conversion without knowing the $value $type. It will
 * return the $value if it is already an array or convert array-like things
 * including instances of iterable or Arrayable. We intentionally do not
 * cast objects as arrays with (array) because the result can be unexpected
 * with the way PHP handles non-public object properties and considering all
 * anonymous functions are actually object instances of \Closure.
 *
 * @param Arrayable<array-key, mixed>|iterable<mixed, mixed> $value
 * @return array<mixed, mixed>
 */
function array_cast(Arrayable|iterable $value): array
{
    return match (true) {
        \is_array($value) => $value,
        $value instanceof Arrayable => $value->toArray(),
        default => \iterator_to_array($value),
    };
}

/**
 * Return the first value of an array without affecting the internal pointer, or
 * null if the array is empty.
 *
 * @template TValue
 * @param array<array-key, TValue> $value
 * @return TValue|null
 */
function array_first(array $value): mixed
{
    return $value[\array_key_first($value)] ?? null;
}

/**
 * Return the last value of an array without affecting the internal pointer, or
 * null if the array is empty.
 *
 * @template TValue
 * @param array<array-key, TValue> $value
 * @return TValue|null
 */
function array_last(array $value): mixed
{
    return $value[\array_key_last($value)] ?? null;
}

/**
 * Check if a key is set and has a non-null value from an arbitrary array or
 * object that implements the ArrayAccess interface, supporting dot notation
 * to search a deeply nested array with a composite string key.
 *
 * @template TKey of array-key
 * @template TValue
 * @param array<TValue>|\ArrayAccess<TKey, TValue> $array
 */
function array_has(string $key, array|\ArrayAccess $array): bool
{
    return array_get($key, $array) !== null;
}

/**
 * Check if any of the paths exist in the array or object that implements
 * the ArrayAccess interface, supporting dot notation to search a deeply
 * nested array with a composite string key.
 *
 * @template TKey of array-key
 * @template TValue
 * @param array<string> $paths
 * @param array<TValue>|\ArrayAccess<TKey, TValue> $array
 */
function array_has_any(array $paths, array|\ArrayAccess $array): bool
{
    foreach ($paths as $path) {
        if (array_has($path, $array)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if all the paths exist in the array or object that implements
 * the ArrayAccess interface, supporting dot notation to search a deeply
 * nested array with a composite string key.
 *
 * @template TKey of array-key
 * @template TValue
 * @param array<string> $paths
 * @param array<TValue>|\ArrayAccess<TKey, TValue> $array
 */
function array_has_all(array $paths, array|\ArrayAccess $array): bool
{
    foreach ($paths as $path) {
        if (! array_has($path, $array)) {
            return false;
        }
    }

    return true;
}

/**
 * Look up a value from an arbitrary array or object that implements the
 * ArrayAccess interface, supporting dot notation to search a deeply nested
 * array with a composite string key. If the key does not exist or is null,
 * the default value will be returned. If the $default argument is
 * `callable`, it will be evaluated and the result returned.
 *
 * @template TKey of array-key
 * @template TValue
 * @param array<TValue>|\ArrayAccess<TKey, TValue> $array
 * @return TValue|null
 */
function array_get(string $key, array|\ArrayAccess $array): mixed
{
    // If the value explicitly exists, even if it has dots, return it early.
    if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (\explode('.', $key) as $subkey) {
        if (! isset($array[$subkey])) {
            return null;
        }
        $array = $array[$subkey];
    }

    return $array;
}

function array_dot(array $array): array
{
    $results = [];
    array_dot_flatten($results, $array, '');
    return $results;
}

function array_dot_flatten(array &$results, iterable $array, string $prefix): void
{
    foreach ($array as $key => $value) {
        $key = $prefix . $key;
        if (\is_iterable($value) && $value) {
            array_dot_flatten($results, $value, $key . '.');
        } else {
            $results[$key] = $value;
        }
    }
}

/**
 * Returns the passed value, recursively casting instances of `Arrayable` and
 * `Traversable` into arrays.
 */
function array_value(mixed $value): mixed
{
    return is_arrayable($value) ? \array_map(__FUNCTION__, array_cast($value)) : $value;
}

/**
 * @template T
 * @param T $value
 * @return ($value is array ? T&array : array<T>)
 */
function array_wrap(mixed $value): array
{
    return \is_array($value) ? $value : [$value];
}

/**
 * @return array<mixed>
 */
function array_convert_nested_objects(mixed $value): array
{
    try {
        $encoded = \json_encode($value, \JSON_THROW_ON_ERROR);
        $decoded = \json_decode($encoded, true, 512, \JSON_THROW_ON_ERROR);
        return array_wrap($decoded);
    } catch (\JsonException) {
        return [];
    }
}

/**
 * Maps a callback on each element of an iterable, where the first parameter
 * of the callback is the value and the second parameter is the key, returning
 * an array.
 *
 * @template TKey of array-key
 * @template TValue
 * @template TReturn
 * @param callable(TValue, TKey): TReturn $callback
 * @param iterable<TKey, TValue> $iterable
 * @return array<TKey, TReturn>
 */
function array_map_with_key(callable $callback, iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$key] = $callback($value, $key);
    }

    return $result;
}

function array_is_sorted(array $array, Order $order = Order::Ascending): bool
{
    if (\count($array) < 2) {
        return true;
    }

    $invalid = $order === Order::Ascending ? 1 : -1;
    $prev = \reset($array);
    $curr = \next($array);

    do {
        if (($prev <=> $curr) === $invalid) {
            return false;
        }
        $prev = $curr;
        $curr = \next($array);
    } while (\key($array) !== null);

    return true;
}

/**
 * Similar to `array_any`, but operates on any iterable, the callback only
 * accepts a single parameter, the value, and returns true if any
 * element in the iterable passes the callback test.
 *
 * @template TKey of array-key
 * @template TValue
 * @param callable(TValue): bool $callback
 * @param iterable<TKey, TValue> $iterable
 */
function array_any_value(iterable $iterable, callable $callback): bool
{
    foreach ($iterable as $value) {
        if ($callback($value)) {
            return true;
        }
    }

    return false;
}

/**
 * Similar to `array_any`, but operates on any iterable, the callback only
 * accepts a single parameter, the key, and returns true if any
 * element in the iterable passes the callback test.
 *
 * @template TKey of array-key
 * @template TValue
 * @param callable(TKey): bool $callback
 * @param iterable<TKey, TValue> $iterable
 */
function array_any_key(iterable $iterable, callable $callback): bool
{
    foreach ($iterable as $key => $_) {
        if ($callback($key)) {
            return true;
        }
    }

    return false;
}
