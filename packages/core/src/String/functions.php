<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use Ramsey\Uuid\UuidInterface;

/**
 * Casts the passed argument to a string if it is a string, scalar, null, or
 * instance of \Stringable.
 *
 * Note, you probably want to use `strval(...)` instead of this function if you
 * don't need the additional checks and handling.
 */
function str_cast(mixed $string): string
{
    if (\is_string($string)) {
        return $string;
    }

    if ($string === null || \is_scalar($string) || $string instanceof \Stringable) {
        return (string)$string;
    }

    throw new \InvalidArgumentException('$string Must Be String, Stringable, or Implement __toString');
}

/**
 * The inverse of `str_cast`; convert a string to a `Stringable` object
 * or return the object if it is already an instance of `Stringable`.
 */
function str_to_stringable(mixed $string): \Stringable
{
    return $string instanceof \Stringable ? $string : new readonly class (str_cast($string)) implements \Stringable {
        public function __construct(private string $string)
        {
        }

        public function __toString(): string
        {
            return $this->string;
        }
    };
}

/**
 * Convert a string or `Stringable` object to an in-memory stream resource.
 *
 * @return resource
 */
function str_to_stream(\Stringable|string|int|float|bool|null $string = ''): mixed
{
    $stream = \fopen('php://memory', 'r+b') ?: throw new \RuntimeException('Unable To Open Memory Stream');
    \fwrite($stream, (string)$string);
    \rewind($stream);
    return $stream;
}

/**
 * Trim whitespace characters from both sides of a given string. An array of
 * additional characters to trim off can be passed as the second parameter.
 *
 * @param array<string> $additional_chars
 */
function str_trim(string $string, array $additional_chars = []): string
{
    return \trim($string, " \t\n\r\0\x0B" . \implode('', $additional_chars));
}

/**
 * Trim whitespace characters from the right side of a string. An array of
 * additional characters to trim off can be passed as the second parameter.
 *
 * @param array<string> $additional_chars
 */
function str_rtrim(string $string, array $additional_chars = []): string
{
    return \rtrim($string, " \t\n\r\0\x0B" . \implode('', $additional_chars));
}

/**
 * Trim whitespace characters from the left side of a string. An array of
 * additional characters to trim off can be passed as the second parameter.
 *
 * @param array<string> $additional_chars
 */
function str_ltrim(string $string, array $additional_chars = []): string
{
    return \ltrim($string, " \t\n\r\0\x0B" . \implode('', $additional_chars));
}

function str_truncate(
    string|\Stringable $string,
    int $max_length = 80,
    string $trim_marker = '...',
): string {
    $max_length >= 0 || throw new \UnexpectedValueException('Max Length Must Be Non-Negative');
    \strlen($trim_marker) <= $max_length || throw new \UnexpectedValueException('Trim Marker Length Must Be Less Than or Equal to Max Length');

    return \mb_strimwidth((string)$string, 0, $max_length, $trim_marker);
}

/**
 * Concatenate the `$prefix` string to the start of the `$string` string, if
 * the `$string` does not already start with the `$prefix`, e.g.:
 *    str_start("path/to/something", "/"); // "/path/to/something"
 *    str_start("/path/to/something", "/"); // "/path/to/something"
 */
function str_prefix(string $string, string $prefix): string
{
    return \str_starts_with($string, $prefix) ? $string : $prefix . $string;
}

/**
 * Concatenate the `$prefix` string to the end of the `$string` string, if
 * the `$string` does not already end with the `$prefix`, e.g.:
 *    str_end("path/to/something", "/"); // "path/to/something/"
 *    str_end("path/to/something/", "/"); // "path/to/something/"
 */
function str_suffix(string $string, string $suffix): string
{
    return \str_ends_with($string, $suffix) ? $string : $string . $suffix;
}

function str_strip(string $string, RegExp|string $search): string
{
    if (\is_string($search)) {
        return \str_replace($search, '', $string);
    }

    $result = @\preg_replace((string)$search, '', $string);
    if ($result === null) {
        // https://www.php.net/manual/en/pcre.constants.php
        throw new \RuntimeException('preg_replace() returned error code ' . \preg_last_error());
    }
    return $result;
}

function str_snake(string $string): string
{
    return StringCase::Snake->from($string);
}

function str_kabob(string $string): string
{
    return StringCase::Kabob->from($string);
}

function str_pascal(string $string): string
{
    return StringCase::Pascal->from($string);
}

function str_camel(string $string): string
{
    return StringCase::Camel->from($string);
}

function str_screaming(string $string): string
{
    return StringCase::Screaming->from($string);
}

function str_dot(string $string): string
{
    return StringCase::Dot->from($string);
}

function str_ucwords(string $string): string
{
    return StringCase::Title->from($string);
}

function str_enquote(string $string, string $char = '"'): string
{
    return $char . $string . $char;
}

function str_rpad(
    \Stringable|string|int|float|null $string,
    int $length,
    string $pad_string = " ",
): string {
    return \str_pad((string)$string, $length, $pad_string, \STR_PAD_RIGHT);
}

function str_lpad(
    \Stringable|string|int|float|null $string,
    int $length,
    string $pad_string = " ",
): string {
    return \str_pad((string)$string, $length, $pad_string, \STR_PAD_LEFT);
}

/**
 * Takes a fully qualified, qualified, relative, or unqualified class name
 * and returns the unqualified name of the class without the namespace.
 */
function class_shortname(object|string $classname): string
{
    if (\is_object($classname)) {
        $classname = $classname::class;
    }

    if (! \str_contains($classname, '\\')) {
        return $classname;
    }

    return \ltrim((string)\strrchr($classname, '\\'), '\\');
}

function bytes(\Stringable|BinaryString|UuidInterface|string $value): string
{
    return match (true) {
        \is_string($value) => $value,
        $value instanceof BinaryString => $value->bytes(),
        $value instanceof UuidInterface => $value->getBytes(),
        default => (string)$value,
    };
}
