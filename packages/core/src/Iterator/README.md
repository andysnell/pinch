# Iterator Functions

This module provides pure functions for working with iterators, replacing the static methods from the `Iter` class.

## Functions

### `iter_cast(Arrayable|iterable $value): \Iterator`

Converts any iterable value to an Iterator. This function handles arrays, existing iterators, traversable objects, and arrayable objects.

```php
use function PhoneBurner\Pinch\Iterator\iter_cast;

$array = ['a' => 1, 'b' => 2];
$iterator = iter_cast($array); // Returns ArrayIterator

$existingIterator = new ArrayIterator([1, 2, 3]);
$result = iter_cast($existingIterator); // Returns the same iterator
```

### `iter_first(iterable $iter): mixed`

Returns the first element of an iterable, or `null` if empty.

```php
use function PhoneBurner\Pinch\Iterator\iter_first;

iter_first([1, 2, 3]); // Returns 1
iter_first([]); // Returns null
```

### `iter_last(iterable $iter): mixed`

Returns the last element of an iterable, or `null` if empty.

```php
use function PhoneBurner\Pinch\Iterator\iter_last;

iter_last([1, 2, 3]); // Returns 3
iter_last([]); // Returns null
```

### `iter_map(callable $callback, iterable $iter): \Generator`

Maps a callback over an iterable, where the callback receives both value and key.

```php
use function PhoneBurner\Pinch\Iterator\iter_map;

$result = iter_map(
    fn($value, $key) => $value * 2,
    [1, 2, 3]
);
// Returns Generator yielding [0 => 2, 1 => 4, 2 => 6]
```

### `iter_amap(callable $callback, iterable $iter): array`

Maps a callback over an iterable and returns an array (array map).

```php
use function PhoneBurner\Pinch\Iterator\iter_amap;

$result = iter_amap(fn($value) => $value * 2, [1, 2, 3]);
// Returns [0 => 2, 1 => 4, 2 => 6]
```

### `iter_chain(iterable|Arrayable ...$iterables): \AppendIterator`

Chains multiple iterables together into a single AppendIterator.

```php
use function PhoneBurner\Pinch\Iterator\iter_chain;

$chained = iter_chain([1, 2], [3, 4]);
// Iterating yields: 1, 2, 3, 4
```

### `iter_generate(iterable $iter): \Generator`

Converts an iterable to a Generator using `yield from`.

```php
use function PhoneBurner\Pinch\Iterator\iter_generate;

$generator = iter_generate([1, 2, 3]);
// Returns a Generator that yields the same values
```

### `iter_any_value(callable $callback, iterable $iterable): bool`

Returns `true` if any value in the iterable satisfies the callback condition.

```php
use function PhoneBurner\Pinch\Iterator\iter_any_value;

iter_any_value(fn($value) => $value > 5, [1, 2, 6, 3]); // Returns true
iter_any_value(fn($value) => $value > 10, [1, 2, 3]); // Returns false
```

### `iter_any_key(callable $callback, iterable $iterable): bool`

Returns `true` if any key in the iterable satisfies the callback condition.

```php
use function PhoneBurner\Pinch\Iterator\iter_any_key;

iter_any_key(fn($key) => $key === 'target', ['a' => 1, 'target' => 2]); // Returns true
iter_any_key(fn($key) => $key === 'missing', ['a' => 1, 'b' => 2]); // Returns false
```

## Migration from Iter Class

| Old Method         | New Function       |
| ------------------ | ------------------ |
| `Iter::cast()`     | `iter_cast()`      |
| `Iter::first()`    | `iter_first()`     |
| `Iter::last()`     | `iter_last()`      |
| `Iter::map()`      | `iter_map()`       |
| `Iter::amap()`     | `iter_amap()`      |
| `Iter::chain()`    | `iter_chain()`     |
| `Iter::generate()` | `iter_generate()`  |
| `Iter::anyValue()` | `iter_any_value()` |
| `Iter::anyKey()`   | `iter_any_key()`   |

## Usage Notes

- All functions maintain the same type safety and generics as the original static methods
- Functions are automatically loaded via Composer's autoloading
- Use `use function` statements to import specific functions you need
- Functions follow the same naming convention: `iter_` prefix with snake_case
