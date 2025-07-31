# String Functions

This module provides pure functions for string manipulation and conversion, replacing the static methods from the `Str` class.

## Functions

### `str_stringable(mixed $string): bool`

Checks if a value is a string or implements Stringable.

```php
use function PhoneBurner\Pinch\String\is_stringable;

is_stringable('hello'); // true
is_stringable($stringableObject); // true
is_stringable(123); // false
```

### `str_cast(mixed $string): string`

Converts a value to string if it's string, scalar, null, or Stringable.

```php
use function PhoneBurner\Pinch\String\str_cast;

str_cast('hello'); // 'hello'
str_cast(123); // '123'
str_cast(null); // ''
str_cast(true); // '1'
```

### `str_object(mixed $string): \Stringable`

Converts a string to a Stringable object or returns existing Stringable.

```php
use function PhoneBurner\Pinch\String\str_to_stringable;

$stringable = str_to_stringable('hello');
echo $stringable; // 'hello'
```

## Trimming Functions

### `str_trim(string $string, array $additional_chars = []): string`

Trims whitespace and optionally additional characters from both sides.

```php
use function PhoneBurner\Pinch\String\str_trim;

str_trim('  hello  '); // 'hello'
str_trim('--hello--', ['-']); // 'hello'
```

### `str_rtrim(string $string, array $additional_chars = []): string`

Trims from the right side only.

### `str_ltrim(string $string, array $additional_chars = []): string`

Trims from the left side only.

## String Modification Functions

### `str_start(string $string, string $prefix): string`

Ensures a string starts with a prefix.

```php
use function PhoneBurner\Pinch\String\str_prefix;

str_prefix('path/to/file', '/'); // '/path/to/file'
str_prefix('/path/to/file', '/'); // '/path/to/file' (unchanged)
```

### `str_end(string $string, string $suffix): string`

Ensures a string ends with a suffix.

```php
use function PhoneBurner\Pinch\String\str_suffix;

str_suffix('path/to/file', '/'); // 'path/to/file/'
str_suffix('path/to/file/', '/'); // 'path/to/file/' (unchanged)
```

### `str_truncate(string|\Stringable $string, int $max_length = 80, string $trim_marker = '...'): string`

Truncates a string to a maximum length with an optional marker.

```php
use function PhoneBurner\Pinch\String\str_truncate;

str_truncate('This is a very long string', 10); // 'This is...'
str_truncate('Short', 10); // 'Short' (unchanged)
```

### `str_strip(string $string, RegExp|string $search): string`

Removes all occurrences of a string or regex pattern.

```php
use function PhoneBurner\Pinch\String\str_strip;

str_strip('hello world', 'o'); // 'hell wrld'
```

## Case Conversion Functions

### `str_snake(string $string): string`

Converts a string to snake_case.

```php
use function PhoneBurner\Pinch\String\str_snake;

str_snake('HelloWorld'); // 'hello_world'
str_snake('helloWorld'); // 'hello_world'
str_snake('hello-world'); // 'hello_world'
```

### `str_camel(string $string): string`

Converts a string to camelCase.

```php
use function PhoneBurner\Pinch\String\str_camel;

str_camel('hello_world'); // 'helloWorld'
str_camel('hello-world'); // 'helloWorld'
```

### `str_pascal(string $string): string`

Converts a string to PascalCase.

```php
use function PhoneBurner\Pinch\String\str_pascal;

str_pascal('hello_world'); // 'HelloWorld'
str_pascal('hello-world'); // 'HelloWorld'
```

### `str_kabob(string $string): string`

Converts a string to kabob-case.

```php
use function PhoneBurner\Pinch\String\str_kabob;

str_kabob('HelloWorld'); // 'hello-world'
str_kabob('hello_world'); // 'hello-world'
```

### `str_screaming(string $string): string`

Converts a string to SCREAMING_SNAKE_CASE.

```php
use function PhoneBurner\Pinch\String\str_screaming;

str_screaming('HelloWorld'); // 'HELLO_WORLD'
```

### `str_dot(string $string): string`

Converts a string to dot.case.

```php
use function PhoneBurner\Pinch\String\str_dot;

str_dot('HelloWorld'); // 'hello.world'
```

### `str_ucwords(string $string): string`

Converts a string to Title Case.

```php
use function PhoneBurner\Pinch\String\str_ucwords;

str_ucwords('hello_world'); // 'Hello World'
```

## Utility Functions

### `str_shortname(string $classname): string`

Extracts the class name from a fully qualified class name.

```php
use function PhoneBurner\Pinch\String\class_shortname;

class_shortname('App\\Models\\User'); // 'User'
class_shortname('User'); // 'User' (unchanged)
```

### `str_enquote(string $string, string $char = '"'): string`

Wraps a string in quotes.

```php
use function PhoneBurner\Pinch\String\str_enquote;

str_enquote('hello'); // '"hello"'
str_enquote('hello', "'"); // "'hello'"
```

### `str_rpad(string|int|float|null $string, int $length, string $pad_string = ' '): string`

Pads a string to the right.

```php
use function PhoneBurner\Pinch\String\str_rpad;

str_rpad('hello', 10); // 'hello     '
str_rpad('hello', 10, '0'); // 'hello00000'
```

### `str_lpad(string|int|float|null $string, int $length, string $pad_string = ' '): string`

Pads a string to the left.

```php
use function PhoneBurner\Pinch\String\str_lpad;

str_lpad('hello', 10); // '     hello'
str_lpad('hello', 10, '0'); // '00000hello'
```

## Usage Notes

- All functions maintain the same type safety and parameter signatures as the original static methods
- Functions are automatically loaded via Composer's autoloading
- Use `use function` statements to import specific functions you need
- Functions follow the same naming convention: `str_` prefix with snake_case
- Case conversion functions use a tokenization approach that handles various input formats consistently
