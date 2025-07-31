<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math;

use Random\IntervalBoundary;

/**
 * The PHP builtin \floor() function rounds numbers down to the next lowest
 * integer, but for historic "pre-type-sanity era" reasons, returns a float;
 * however, most places we end up using it want a strict integer-typed value.
 */
function int_floor(int|float $number): int
{
    return (int)\floor($number);
}

/**
 * The PHP builtin \ceil() function rounds numbers down to the next lowest
 * integer, but for historic "pre-type-sanity era" reasons, returns a float;
 * however, most places we end up using it want a strict integer-typed value.
 */
function int_ceil(int|float $number): int
{
    return (int)\ceil($number);
}

function clamp(int|float $value, int|float $min, int|float $max): int|float
{
    return match (true) {
        $max < $min => throw new \UnexpectedValueException('max must be greater than or equal to min'),
        $value <= $min => $min,
        $value >= $max => $max,
        default => $value,
    };
}

/**
 * Clamps a value to the integer value within the specified range.
 */
function int_clamp(int|float $value, int $min, int $max): int
{
    return match (true) {
        $max < $min => throw new \UnexpectedValueException('max must be greater than or equal to min'),
        $value <= $min => $min,
        $value >= $max => $max,
        default => (int)$value,
    };
}

function is_between(
    int|float $value,
    int|float $min,
    int|float $max,
    IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
): bool {
    return match ($boundary) {
        IntervalBoundary::ClosedClosed => $value >= $min && $value <= $max,
        IntervalBoundary::OpenOpen => $value > $min && $value < $max,
        IntervalBoundary::OpenClosed => $value > $min && $value <= $max,
        IntervalBoundary::ClosedOpen => $value >= $min && $value < $max,
    };
}

function is_int_between(
    int $value,
    int $min,
    int $max,
    IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
): bool {
    return match ($boundary) {
        IntervalBoundary::ClosedClosed => $value >= $min && $value <= $max,
        IntervalBoundary::OpenOpen => $value > $min && $value < $max,
        IntervalBoundary::OpenClosed => $value > $min && $value <= $max,
        IntervalBoundary::ClosedOpen => $value >= $min && $value < $max,
    };
}

/**
 * Returns true when the value is:
 *  - a float (trivial case)
 *  - an integer that can be converted to a float without loss of precision
 *  - a string that represents a valid float, e.g. "3.14", "1.0", "1e10", "-0.001"
 *  - a string that represents an integer, (e.g., "42", "-7") and can be converted without loss of precision
 *  - an object that implements __toString() and returns a valid float, e.g. new Decimal("3.14")
 *
 * Integers can be represented exactly as IEEE 754 floats when:
 *  - The number is a pure power of 2 (2^0, 2^1, 2^2, ..., up to 2^1023)
 *  - abs($value) <= 2^53 - 1
 *  - 2^53 <= abs($value) <= 2^54 && $value % 2 === 0
 *  - 2^54 <= abs($value) <= 2^55 && $value % 4 === 0
 *  - 2^55 <= abs($value) <= 2^56 && $value % 8 === 0
 * And so on...
 */
function is_representable_as_float(mixed $value): bool
{
    if (\is_float($value)) {
        return true;
    }

    if (\is_int($value)) {
        return is_integer_representable_as_float($value);
    }

    if ($value instanceof \Stringable) {
        $value = (string)$value;
    }

    if (\is_string($value)) {
        return is_string_representable_as_float($value);
    }

    return false;
}

/**
 * Check if an integer can be exactly represented in IEEE 754 double precision.
 * Uses pure integer arithmetic to avoid floating-point precision issues.
 */
function is_integer_representable_as_float(int $value): bool
{
    $abs_value = \abs($value);

    // All integers up to 2^53 - 1 are exactly representable
    if ($abs_value <= 9007199254740991) { // 2^53 - 1
        return true;
    }

    // Check if it's a pure power of 2 using bit manipulation
    if (($abs_value & $abs_value - 1) === 0) {
        // For powers of 2, they are representable up to the maximum finite value
        // Since PHP integers are 64-bit signed, the largest power of 2 that fits
        // is 2^62, and all powers of 2 that fit in a PHP integer are representable
        return true;
    }

    // For larger integers, use IEEE 754 precision pattern
    // From 2^n to 2^(n+1), only values divisible by 2^(n-52) are representable

    // Find the position of the most significant bit using integer operations
    $msb_position = find_msb_position($abs_value);

    if ($msb_position >= 53) {
        // Calculate required granularity: 2^(msb_position - 52)
        $shift_amount = $msb_position - 52;

        // For very large shift amounts beyond what we can handle
        if ($shift_amount >= 63) {
            // Only 0 would be divisible, but we already handled values <= 2^53-1 above
            return false;
        }

        // Check if the number is divisible by the required granularity
        // This is equivalent to checking if the lower (shift_amount) bits are zero
        $granularity = 1 << $shift_amount;
        return $abs_value % $granularity === 0;
    }

    return false;
}

/**
 * Find the position of the most significant bit using pure integer arithmetic.
 * Returns the 0-based position of the MSB.
 */
function find_msb_position(int $value): int
{
    if ($value === 0) {
        return 0;
    }

    $position = 0;

    // Binary search approach to find MSB position efficiently
    if ($value >= (1 << 32)) {
        $position += 32;
        $value >>= 32;
    }
    if ($value >= (1 << 16)) {
        $position += 16;
        $value >>= 16;
    }
    if ($value >= (1 << 8)) {
        $position += 8;
        $value >>= 8;
    }
    if ($value >= (1 << 4)) {
        $position += 4;
        $value >>= 4;
    }
    if ($value >= (1 << 2)) {
        $position += 2;
        $value >>= 2;
    }
    if ($value >= (1 << 1)) {
        $position += 1;
    }

    return $position;
}

/**
 * Check if a string can be represented as a float.
 * Uses consistent validation approach across all functions.
 */
function is_string_representable_as_float(string $value): bool
{
    // Trim whitespace for consistent parsing
    $trimmed_value = \trim($value);

    // Handle special float values that aren't recognized by is_numeric
    if (
        \strcasecmp($trimmed_value, 'INF') === 0 ||
        \strcasecmp($trimmed_value, '-INF') === 0 ||
        \strcasecmp($trimmed_value, 'NAN') === 0
    ) {
        return true;
    }

    if (! \is_numeric($trimmed_value)) {
        return false;
    }

    // Check if it's a valid float string
    $float_value = \filter_var($trimmed_value, \FILTER_VALIDATE_FLOAT);
    if ($float_value === false) {
        return false;
    }

    // Check if it represents an integer (no decimal point, no scientific notation)
    // and fits within PHP_INT_MIN to PHP_INT_MAX range
    $int_value = \filter_var($trimmed_value, \FILTER_VALIDATE_INT, [
        'flags' => \FILTER_NULL_ON_FAILURE,
        'options' => [
            'min_range' => \PHP_INT_MIN,
            'max_range' => \PHP_INT_MAX,
        ],
    ]);

    if ($int_value !== null) {
        // It's an integer string, apply integer representability rules
        return is_integer_representable_as_float($int_value);
    }

    // It's a float string (with decimal point or scientific notation)
    // All valid float strings are representable as floats
    return true;
}

/**
 * Returns true when the value can be represented as an integer:
 *  - The value is an integer
 *  - The value is a float not greater than PHP_INT_MAX and not less than PHP_INT_MIN, without fractional part;
 *  - The value is a string that represents a valid integer and can be converted to an integer within the range of PHP_INT_MIN and PHP_INT_MAX.
 */
function is_representable_as_int(mixed $value): bool
{
    if (\is_int($value)) {
        return true;
    }

    if (\is_float($value)) {
        return is_float_representable_as_int($value);
    }

    if ($value instanceof \Stringable) {
        $value = (string)$value;
    }

    if (\is_string($value)) {
        return is_string_representable_as_int($value);
    }

    return false;
}

/**
 * Check if a float can be represented as an integer.
 * Uses consistent validation approach.
 */
function is_float_representable_as_int(float $value): bool
{
    if (! \is_finite($value)) {
        return false;
    }

    // Check if the float has no fractional part
    if (\floor($value) !== $value) {
        return false;
    }

    // Check range boundaries more precisely
    if ($value > (float)\PHP_INT_MAX || $value < (float)\PHP_INT_MIN) {
        return false;
    }

    // For values right at the boundary, check if casting to int and back
    // to float preserves the original value
    $int_value = (int)$value;
    return ((float)$int_value) === $value;
}

/**
 * Check if a string can be represented as an integer.
 * Uses consistent validation approach.
 */
function is_string_representable_as_int(string $value): bool
{
    // Trim whitespace for consistent parsing
    $trimmed_value = \trim($value);

    if (! \is_numeric($trimmed_value)) {
        return false;
    }

    $validated_int = \filter_var($trimmed_value, \FILTER_VALIDATE_INT, [
        'flags' => \FILTER_NULL_ON_FAILURE,
        'options' => [
            'min_range' => \PHP_INT_MIN,
            'max_range' => \PHP_INT_MAX,
        ],
    ]);

    return $validated_int !== null;
}

/**
 * Converts a value to float if it can be represented without precision loss.
 *
 * Returns the float representation if is_representable_as_float() would return true,
 * otherwise returns null.
 */
function represent_as_float(mixed $value): float|null
{
    if (! is_representable_as_float($value)) {
        return null;
    }

    if (\is_float($value)) {
        return $value;
    }

    if (\is_int($value)) {
        return (float)$value;
    }

    if ($value instanceof \Stringable) {
        $value = (string)$value;
    }

    if (\is_string($value)) {
        $trimmed_value = \trim($value);

        // Handle special float values that aren't recognized by filter_var
        if (\strcasecmp($trimmed_value, 'INF') === 0) {
            return \INF;
        }
        if (\strcasecmp($trimmed_value, '-INF') === 0) {
            return -\INF;
        }
        if (\strcasecmp($trimmed_value, 'NAN') === 0) {
            return \NAN;
        }

        $float_value = \filter_var($trimmed_value, \FILTER_VALIDATE_FLOAT);
        return $float_value !== false ? $float_value : null;
    }

    return null;
}

/**
 * Converts a value to int if it can be represented without loss.
 *
 * Returns the integer representation if is_representable_as_int() would return true,
 * otherwise returns null.
 */
function represent_as_int(mixed $value): int|null
{
    if (! is_representable_as_int($value)) {
        return null;
    }

    if (\is_int($value)) {
        return $value;
    }

    if (\is_float($value)) {
        return (int)$value;
    }

    if ($value instanceof \Stringable) {
        $value = (string)$value;
    }

    if (\is_string($value)) {
        $trimmed_value = \trim($value);
        $int_value = \filter_var($trimmed_value, \FILTER_VALIDATE_INT, [
            'flags' => \FILTER_NULL_ON_FAILURE,
            'options' => [
                'min_range' => \PHP_INT_MIN,
                'max_range' => \PHP_INT_MAX,
            ],
        ]);

        return $int_value ?? null;
    }

    return null;
}
