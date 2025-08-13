# Array Functions

This module provides pure functions for working with arrays and array-like structures, replacing the static methods from the `Arr` class.

## Functions

### `arr_accessible(mixed $array): bool`

Returns `true` if the value is an array or implements ArrayAccess.

```php
use function PhoneBurner\Pinch\Array\arr_accessible;

arr_accessible([]); // true
arr_accessible(new ArrayObject()); // true
arr_accessible('string'); // false
```

### `arr_arrayable(mixed $value): bool`

Returns `true` if the value can be converted to an array (array, Traversable, or Arrayable).

```php
use function PhoneBurner\Pinch\Array\is_arrayable;

is_arrayable([]); // true
is_arrayable(new ArrayIterator([1, 2, 3])); // true
is_arrayable($arrayable_object); // true
```

### `arr_cast(Arrayable|iterable $value): array`

Converts array-like values to actual arrays.

```php
use function PhoneBurner\Pinch\Array\array_cast;

array_cast([1, 2, 3]); // Returns [1, 2, 3]
array_cast(new ArrayIterator(['a' => 1, 'b' => 2])); // Returns ['a' => 1, 'b' => 2]
array_cast($arrayable_object); // Returns $arrayable_object->toArray()
```

### `arr_first(iterable|Arrayable $value): mixed`

Returns the first element of an array-like value, or `null` if empty.

```php
use function PhoneBurner\Pinch\Array\array_first;

array_first([1, 2, 3]); // Returns 1
array_first(['key' => 'value']); // Returns 'value'
array_first([]); // Returns null
```

### `arr_last(iterable|Arrayable $value): mixed`

Returns the last element of an array-like value, or `null` if empty.

```php
use function PhoneBurner\Pinch\Array\arr_last;

arr_last([1, 2, 3]); // Returns 3
arr_last(['key' => 'value']); // Returns 'value'
arr_last([]); // Returns null
```

### `arr_has(string $key, array|\ArrayAccess $array): bool`

Checks if a key exists in an array or ArrayAccess object, supporting dot notation.

```php
use function PhoneBurner\Pinch\Array\array_has;

$data = ['user' => ['name' => 'John', 'email' => 'john@example.com']];

array_has('user', $data); // true
array_has('user.name', $data); // true
array_has('user.age', $data); // false
```

### `arr_has_any(array $paths, array|\ArrayAccess $array): bool`

Checks if any of the given paths exist in the array.

```php
use function PhoneBurner\Pinch\Array\array_has_any;

$data = ['user' => ['name' => 'John']];

array_has_any(['user.name', 'user.age'], $data); // true (name exists)
array_has_any(['user.age', 'user.phone'], $data); // false (neither exists)
```

### `arr_has_all(array $paths, array|\ArrayAccess $array): bool`

Checks if all of the given paths exist in the array.

```php
use function PhoneBurner\Pinch\Array\array_has_all;

$data = ['user' => ['name' => 'John', 'email' => 'john@example.com']];

array_has_all(['user.name', 'user.email'], $data); // true
array_has_all(['user.name', 'user.age'], $data); // false (age missing)
```

### `arr_get(string $key, array|\ArrayAccess $array): mixed`

Retrieves a value from an array using dot notation, returns `null` if not found.

```php
use function PhoneBurner\Pinch\Array\array_get;

$data = ['user' => ['name' => 'John', 'address' => ['city' => 'NYC']]];

array_get('user.name', $data); // Returns 'John'
array_get('user.address.city', $data); // Returns 'NYC'
array_get('user.age', $data); // Returns null
```

### `arr_dot(array $array): array`

Flattens a nested array using dot notation for keys.

```php
use function PhoneBurner\Pinch\Array\array_dot;

$nested = [
    'user' => [
        'name' => 'John',
        'address' => ['city' => 'NYC', 'zip' => '10001']
    ]
];

array_dot($nested);
// Returns:
// [
//     'user.name' => 'John',
//     'user.address.city' => 'NYC',
//     'user.address.zip' => '10001'
// ]
```

### `arr_value(mixed $value): mixed`

Recursively converts Arrayable and Traversable objects to arrays.

```php
use function PhoneBurner\Pinch\Array\array_value;

$data = [
    'items' => new ArrayObject([1, 2, 3]),
    'nested' => ['more' => new ArrayIterator(['a', 'b'])]
];

array_value($data); // Converts all nested objects to arrays
```

### `arr_wrap(mixed $value): array`

Wraps a value in an array if it's not already array-like.

```php
use function PhoneBurner\Pinch\Array\array_wrap;

array_wrap('string'); // Returns ['string']
array_wrap([1, 2, 3]); // Returns [1, 2, 3] (unchanged)
array_wrap(null); // Returns [null]
```

### `arr_convert_nested_objects(mixed $value): array`

Converts nested objects to arrays using JSON encoding/decoding.

```php
use function PhoneBurner\Pinch\Array\array_convert_nested_objects;

$object = (object) ['name' => 'John', 'nested' => (object) ['age' => 30]];
array_convert_nested_objects($object);
// Returns ['name' => 'John', 'nested' => ['age' => 30]]
```

### `arr_map(callable $callback, iterable $iterable): array`

Maps a callback over an iterable and returns an array.

```php
use function PhoneBurner\Pinch\Array\array_map_with_key;

$result = array_map_with_key(
    fn($value, $key) => $value * 2,
    ['a' => 1, 'b' => 2, 'c' => 3]
);
// Returns ['a' => 2, 'b' => 4, 'c' => 6]
```

## Migration from Arr Class

| Old Method                    | New Function                   |
| ----------------------------- | ------------------------------ |
| `Arr::accessible()`           | `arr_accessible()`             |
| `Arr::arrayable()`            | `arr_arrayable()`              |
| `Arr::cast()`                 | `arr_cast()`                   |
| `Arr::first()`                | `arr_first()`                  |
| `Arr::last()`                 | `arr_last()`                   |
| `Arr::has()`                  | `arr_has()`                    |
| `Arr::hasAny()`               | `arr_has_any()`                |
| `Arr::hasAll()`               | `arr_has_all()`                |
| `Arr::get()`                  | `arr_get()`                    |
| `Arr::dot()`                  | `arr_dot()`                    |
| `Arr::value()`                | `arr_value()`                  |
| `Arr::wrap()`                 | `arr_wrap()`                   |
| `Arr::convertNestedObjects()` | `arr_convert_nested_objects()` |
| `Arr::map()`                  | `arr_map()`                    |

## Usage Notes

- All functions maintain the same type safety and generics as the original static methods
- Functions are automatically loaded via Composer's autoloading
- Use `use function` statements to import specific functions you need
- Functions follow the same naming convention: `arr_` prefix with snake_case
- Dot notation support allows deep array access with simple string keys
