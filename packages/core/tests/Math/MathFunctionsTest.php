<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

use function PhoneBurner\Pinch\Math\clamp;
use function PhoneBurner\Pinch\Math\int_ceil;
use function PhoneBurner\Pinch\Math\int_clamp;
use function PhoneBurner\Pinch\Math\int_floor;
use function PhoneBurner\Pinch\Math\is_between;
use function PhoneBurner\Pinch\Math\is_int_between;
use function PhoneBurner\Pinch\Math\is_representable_as_float;
use function PhoneBurner\Pinch\Math\is_representable_as_int;
use function PhoneBurner\Pinch\Math\represent_as_float;
use function PhoneBurner\Pinch\Math\represent_as_int;

final class MathFunctionsTest extends TestCase
{
    #[Test]
    #[DataProvider('floorProvider')]
    public function intFloorReturnsIntegerFloor(int|float $input, int $expected): void
    {
        self::assertSame($expected, int_floor($input));
    }

    public static function floorProvider(): \Iterator
    {
        yield 'integer' => [5, 5];
        yield 'negative integer' => [-5, -5];
        yield 'positive float' => [5.7, 5];
        yield 'negative float' => [-5.7, -6];
        yield 'zero' => [0, 0];
        yield 'zero point zero' => [0.0, 0];
        yield 'large float' => [1000000.999999, 1000000];
    }

    #[Test]
    #[DataProvider('intCeilProvider')]
    public function intCeilReturnsIntegerCeiling(int|float $input, int $expected): void
    {
        self::assertSame($expected, int_ceil($input));
    }

    public static function intCeilProvider(): \Iterator
    {
        yield 'integer' => [5, 5];
        yield 'negative integer' => [-5, -5];
        yield 'positive float' => [5.7, 6];
        yield 'negative float' => [-5.7, -5];
        yield 'zero' => [0, 0];
        yield 'zero point zero' => [0.0, 0];
        yield 'large float' => [1000000.000001, 1000001];
    }

    #[Test]
    #[DataProvider('clampProvider')]
    public function clampConstrainsValueWithinRange(
        int|float $value,
        int|float $min,
        int|float $max,
        int|float $expected,
    ): void {
        self::assertSame($expected, clamp($value, $min, $max));
    }

    public static function clampProvider(): \Iterator
    {
        yield 'within range' => [5, 0, 10, 5];
        yield 'at min' => [0, 0, 10, 0];
        yield 'at max' => [10, 0, 10, 10];
        yield 'below min' => [-5, 0, 10, 0];
        yield 'above max' => [15, 0, 10, 10];
        yield 'float within range' => [5.5, 0, 10, 5.5];
        yield 'float below min' => [-5.5, 0, 10, 0];
        yield 'float above max' => [15.5, 0, 10, 10];
        yield 'negative range' => [-15, -20, -10, -15];
        yield 'zero range' => [5, 5, 5, 5];
    }

    #[Test]
    public function clampThrowsExceptionWhenMaxLessThanMin(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');
        clamp(5, 10, 0);
    }

    #[Test]
    #[DataProvider('intClampProvider')]
    public function intClampReturnsIntegerClampedValue(int|float $value, int $min, int $max, int $expected): void
    {
        self::assertSame($expected, int_clamp($value, $min, $max));
    }

    public static function intClampProvider(): \Iterator
    {
        yield 'integer within range' => [5, 0, 10, 5];
        yield 'integer below min' => [-5, 0, 10, 0];
        yield 'integer above max' => [15, 0, 10, 10];
        yield 'float within range' => [5.5, 0, 10, 5];
        yield 'float below min' => [-5.5, 0, 10, 0];
        yield 'float above max' => [15.5, 0, 10, 10];
    }

    #[Test]
    public function intClampThrowsExceptionWhenMaxLessThanMin(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');
        int_clamp(5, 10, 0);
    }

    #[Test]
    #[DataProvider('betweenProvider')]
    public function isBetweenChecksIfValueIsWithinRange(
        int|float $value,
        int|float $min,
        int|float $max,
        IntervalBoundary $boundary,
        bool $expected,
    ): void {
        self::assertSame($expected, is_between($value, $min, $max, $boundary));
    }

    public static function betweenProvider(): \Iterator
    {
        // ClosedClosed [min, max] - both boundaries included
        yield 'ClosedClosed - within range' => [5, 0, 10, IntervalBoundary::ClosedClosed, true];
        yield 'ClosedClosed - at min' => [0, 0, 10, IntervalBoundary::ClosedClosed, true];
        yield 'ClosedClosed - at max' => [10, 0, 10, IntervalBoundary::ClosedClosed, true];
        yield 'ClosedClosed - below min' => [-1, 0, 10, IntervalBoundary::ClosedClosed, false];
        yield 'ClosedClosed - above max' => [11, 0, 10, IntervalBoundary::ClosedClosed, false];

        // OpenOpen (min, max) - both boundaries excluded
        yield 'OpenOpen - within range' => [5, 0, 10, IntervalBoundary::OpenOpen, true];
        yield 'OpenOpen - at min' => [0, 0, 10, IntervalBoundary::OpenOpen, false];
        yield 'OpenOpen - at max' => [10, 0, 10, IntervalBoundary::OpenOpen, false];
        yield 'OpenOpen - below min' => [-1, 0, 10, IntervalBoundary::OpenOpen, false];
        yield 'OpenOpen - above max' => [11, 0, 10, IntervalBoundary::OpenOpen, false];

        // OpenClosed (min, max] - min excluded, max included
        yield 'OpenClosed - within range' => [5, 0, 10, IntervalBoundary::OpenClosed, true];
        yield 'OpenClosed - at min' => [0, 0, 10, IntervalBoundary::OpenClosed, false];
        yield 'OpenClosed - at max' => [10, 0, 10, IntervalBoundary::OpenClosed, true];
        yield 'OpenClosed - below min' => [-1, 0, 10, IntervalBoundary::OpenClosed, false];
        yield 'OpenClosed - above max' => [11, 0, 10, IntervalBoundary::OpenClosed, false];

        // ClosedOpen [min, max) - min included, max excluded
        yield 'ClosedOpen - within range' => [5, 0, 10, IntervalBoundary::ClosedOpen, true];
        yield 'ClosedOpen - at min' => [0, 0, 10, IntervalBoundary::ClosedOpen, true];
        yield 'ClosedOpen - at max' => [10, 0, 10, IntervalBoundary::ClosedOpen, false];
        yield 'ClosedOpen - below min' => [-1, 0, 10, IntervalBoundary::ClosedOpen, false];
        yield 'ClosedOpen - above max' => [11, 0, 10, IntervalBoundary::ClosedOpen, false];

        // Float values
        yield 'ClosedClosed - float within range' => [5.5, 0.0, 10.0, IntervalBoundary::ClosedClosed, true];
        yield 'OpenOpen - float at boundaries' => [5.0, 5.0, 10.0, IntervalBoundary::OpenOpen, false];
        yield 'ClosedOpen - float at max boundary' => [10.0, 0.0, 10.0, IntervalBoundary::ClosedOpen, false];

        // Negative ranges
        yield 'ClosedClosed - negative range' => [-5, -10, -1, IntervalBoundary::ClosedClosed, true];
        yield 'OpenOpen - negative at min' => [-10, -10, -1, IntervalBoundary::OpenOpen, false];
    }

    #[Test]
    public function isBetweenUsesClosedClosedByDefault(): void
    {
        self::assertTrue(is_between(5, 0, 10));
        self::assertTrue(is_between(0, 0, 10));
        self::assertTrue(is_between(10, 0, 10));
        self::assertFalse(is_between(-1, 0, 10));
        self::assertFalse(is_between(11, 0, 10));
    }

    #[Test]
    #[DataProvider('intBetweenProvider')]
    public function isIntBetweenChecksIfIntegerIsWithinRange(
        int $value,
        int $min,
        int $max,
        IntervalBoundary $boundary,
        bool $expected,
    ): void {
        self::assertSame($expected, is_int_between($value, $min, $max, $boundary));
    }

    public static function intBetweenProvider(): \Iterator
    {
        // ClosedClosed [min, max] - both boundaries included
        yield 'ClosedClosed - within range' => [5, 0, 10, IntervalBoundary::ClosedClosed, true];
        yield 'ClosedClosed - at min' => [0, 0, 10, IntervalBoundary::ClosedClosed, true];
        yield 'ClosedClosed - at max' => [10, 0, 10, IntervalBoundary::ClosedClosed, true];
        yield 'ClosedClosed - below min' => [-1, 0, 10, IntervalBoundary::ClosedClosed, false];
        yield 'ClosedClosed - above max' => [11, 0, 10, IntervalBoundary::ClosedClosed, false];

        // OpenOpen (min, max) - both boundaries excluded
        yield 'OpenOpen - within range' => [5, 0, 10, IntervalBoundary::OpenOpen, true];
        yield 'OpenOpen - at min' => [0, 0, 10, IntervalBoundary::OpenOpen, false];
        yield 'OpenOpen - at max' => [10, 0, 10, IntervalBoundary::OpenOpen, false];
        yield 'OpenOpen - below min' => [-1, 0, 10, IntervalBoundary::OpenOpen, false];
        yield 'OpenOpen - above max' => [11, 0, 10, IntervalBoundary::OpenOpen, false];

        // OpenClosed (min, max] - min excluded, max included
        yield 'OpenClosed - within range' => [5, 0, 10, IntervalBoundary::OpenClosed, true];
        yield 'OpenClosed - at min' => [0, 0, 10, IntervalBoundary::OpenClosed, false];
        yield 'OpenClosed - at max' => [10, 0, 10, IntervalBoundary::OpenClosed, true];
        yield 'OpenClosed - below min' => [-1, 0, 10, IntervalBoundary::OpenClosed, false];
        yield 'OpenClosed - above max' => [11, 0, 10, IntervalBoundary::OpenClosed, false];

        // ClosedOpen [min, max) - min included, max excluded
        yield 'ClosedOpen - within range' => [5, 0, 10, IntervalBoundary::ClosedOpen, true];
        yield 'ClosedOpen - at min' => [0, 0, 10, IntervalBoundary::ClosedOpen, true];
        yield 'ClosedOpen - at max' => [10, 0, 10, IntervalBoundary::ClosedOpen, false];
        yield 'ClosedOpen - below min' => [-1, 0, 10, IntervalBoundary::ClosedOpen, false];
        yield 'ClosedOpen - above max' => [11, 0, 10, IntervalBoundary::ClosedOpen, false];

        // Negative ranges
        yield 'ClosedClosed - negative range' => [-5, -10, -1, IntervalBoundary::ClosedClosed, true];
        yield 'OpenOpen - negative at min' => [-10, -10, -1, IntervalBoundary::OpenOpen, false];

        // Edge case: min = max
        yield 'ClosedClosed - min equals max' => [5, 5, 5, IntervalBoundary::ClosedClosed, true];
        yield 'OpenOpen - min equals max' => [5, 5, 5, IntervalBoundary::OpenOpen, false];
        yield 'OpenClosed - min equals max' => [5, 5, 5, IntervalBoundary::OpenClosed, false];
        yield 'ClosedOpen - min equals max' => [5, 5, 5, IntervalBoundary::ClosedOpen, false];
    }

    #[Test]
    public function intBetweenUsesClosedClosedByDefault(): void
    {
        self::assertTrue(is_int_between(5, 0, 10));
        self::assertTrue(is_int_between(0, 0, 10));
        self::assertTrue(is_int_between(10, 0, 10));
        self::assertFalse(is_int_between(-1, 0, 10));
        self::assertFalse(is_int_between(11, 0, 10));
    }

    #[Test]
    #[DataProvider('isRepresentableAsFloatProvider')]
    public function isRepresentableAsFloatReturnsExpectedResult(mixed $value, bool $expected): void
    {
        self::assertSame($expected, is_representable_as_float($value));
    }

    public static function isRepresentableAsFloatProvider(): \Iterator
    {
        // Basic floats - should always return true
        yield 'float 0.0' => [0.0, true];
        yield 'float 3.14' => [3.14, true];
        yield 'float -3.14' => [-3.14, true];
        yield 'float 1.23e10' => [1.23e10, true];
        yield 'float PHP_FLOAT_MAX' => [\PHP_FLOAT_MAX, true];
        yield 'float PHP_FLOAT_MIN' => [\PHP_FLOAT_MIN, true];

        // Basic integers - should return true when representable
        yield 'int 0' => [0, true];
        yield 'int 42' => [42, true];
        yield 'int -42' => [-42, true];
        yield 'int 1000000' => [1000000, true];

        // Powers of 2 - always representable up to 2^1023
        yield 'int 2^10' => [1024, true];
        yield 'int 2^20' => [1048576, true];
        yield 'int 2^30' => [1073741824, true];
        yield 'int 2^52' => [4503599627370496, true];
        yield 'int 2^53' => [9007199254740992, true];
        yield 'int 2^54' => [18014398509481984, true];
        yield 'int 2^55' => [36028797018963968, true];
        yield 'int 2^56' => [72057594037927936, true];

        // Integers up to 2^53 - 1 are exactly representable
        yield 'int 2^53 - 1' => [9007199254740991, true];
        yield 'int 2^53 - 2' => [9007199254740990, true];

        // From 2^53 to 2^54: only even numbers
        yield 'int 2^53 + 1 (odd)' => [9007199254740993, false];
        yield 'int 2^53 + 2 (even)' => [9007199254740994, true];
        yield 'int 2^53 + 3 (odd)' => [9007199254740995, false];
        yield 'int 2^53 + 4 (even)' => [9007199254740996, true];

        // From 2^54 to 2^55: only multiples of 4
        yield 'int 2^54 + 1' => [18014398509481985, false];
        yield 'int 2^54 + 2' => [18014398509481986, false];
        yield 'int 2^54 + 3' => [18014398509481987, false];
        yield 'int 2^54 + 4' => [18014398509481988, true];
        yield 'int 2^54 + 5' => [18014398509481989, false];
        yield 'int 2^54 + 6' => [18014398509481990, false];
        yield 'int 2^54 + 7' => [18014398509481991, false];
        yield 'int 2^54 + 8' => [18014398509481992, true];

        // From 2^55 to 2^56: only multiples of 8
        yield 'int 2^55 + 7' => [36028797018963975, false];
        yield 'int 2^55 + 8' => [36028797018963976, true];
        yield 'int 2^55 + 15' => [36028797018963983, false];
        yield 'int 2^55 + 16' => [36028797018963984, true];

        // String representations of floats
        yield 'string "0.0"' => ['0.0', true];
        yield 'string "3.14"' => ['3.14', true];
        yield 'string "-3.14"' => ['-3.14', true];
        yield 'string "1.23e10"' => ['1.23e10', true];
        yield 'string "1e10"' => ['1e10', true];
        yield 'string "-1.5e-10"' => ['-1.5e-10', true];

        // String representations of integers
        yield 'string "42"' => ['42', true];
        yield 'string "-42"' => ['-42', true];
        yield 'string "1000000"' => ['1000000', true];
        yield 'string "9007199254740992"' => ['9007199254740992', true]; // 2^53

        // Special float values
        yield 'float INF' => [\INF, true];
        yield 'float -INF' => [-\INF, true];
        yield 'float NAN' => [\NAN, true];

        // String representations of special values
        yield 'string "INF"' => ['INF', true];
        yield 'string "-INF"' => ['-INF', true];
        yield 'string "NAN"' => ['NAN', true];

        // Invalid types
        yield 'null' => [null, false];
        yield 'bool true' => [true, false];
        yield 'bool false' => [false, false];
        yield 'array' => [[1, 2, 3], false];
        yield 'empty array' => [[], false];
        yield 'object stdClass' => [new \stdClass(), false];

        // Invalid strings
        yield 'string "abc"' => ['abc', false];
        yield 'string "12.34.56"' => ['12.34.56', false];
        yield 'string "1e"' => ['1e', false];
        yield 'string empty' => ['', false];
        yield 'string with spaces "  42  "' => ['  42  ', true]; // Trimmed strings are valid

        // Stringable objects
        yield 'stringable returning "42.5"' => [new class
        {
            public function __toString(): string
            {
                return '42.5';
            }
        }, true];
        yield 'stringable returning "invalid"' => [new class
        {
            public function __toString(): string
            {
                return 'invalid';
            }
        }, false];

        // Hexadecimal strings (PHP doesn't consider these numeric for float conversion)
        yield 'string "0x10"' => ['0x10', false];
        yield 'string "0xFF"' => ['0xFF', false];

        // Octal strings (PHP doesn't consider these numeric for float conversion)
        yield 'string "010"' => ['010', true]; // Treated as decimal 10, not octal
        yield 'string "0o10"' => ['0o10', false];

        // Large integer that would overflow
        yield 'string "9999999999999999999999999999"' => ['9999999999999999999999999999', true]; // Converts to float
    }

    #[Test]
    #[DataProvider('isRepresentableAsIntProvider')]
    public function isRepresentableAsIntReturnsExpectedResult(mixed $value, bool $expected): void
    {
        self::assertSame($expected, is_representable_as_int($value));
    }

    public static function isRepresentableAsIntProvider(): \Iterator
    {
        // Basic integers - should always return true
        yield 'int 0' => [0, true];
        yield 'int 42' => [42, true];
        yield 'int -42' => [-42, true];
        yield 'int PHP_INT_MAX' => [\PHP_INT_MAX, true];
        yield 'int PHP_INT_MIN' => [\PHP_INT_MIN, true];

        // Floats without fractional parts within range
        yield 'float 0.0' => [0.0, true];
        yield 'float 42.0' => [42.0, true];
        yield 'float -42.0' => [-42.0, true];
        yield 'float 1000000.0' => [1000000.0, true];

        // Floats with fractional parts - should return false
        yield 'float 3.14' => [3.14, false];
        yield 'float -3.14' => [-3.14, false];
        yield 'float 0.1' => [0.1, false];
        yield 'float -0.1' => [-0.1, false];
        yield 'float 42.5' => [42.5, false];

        // Floats at or beyond int limits - large integers lose precision as floats
        yield 'float PHP_INT_MAX' => [(float)\PHP_INT_MAX, false]; // Precision loss when casting to float
        yield 'float PHP_INT_MIN' => [(float)\PHP_INT_MIN, true];
        yield 'float PHP_INT_MAX + 1' => [(float)(\PHP_INT_MAX + 1), false]; // Precision loss
        yield 'float PHP_INT_MIN - 1' => [(float)(\PHP_INT_MIN - 1), true]; // Actually same value due to precision loss

        // String representations of integers
        yield 'string "0"' => ['0', true];
        yield 'string "42"' => ['42', true];
        yield 'string "-42"' => ['-42', true];
        yield 'string "1000000"' => ['1000000', true];
        yield 'string PHP_INT_MAX' => [(string)\PHP_INT_MAX, true];
        yield 'string PHP_INT_MIN' => [(string)\PHP_INT_MIN, true];

        // String representations with leading zeros (filter_var doesn't handle octal/hex)
        yield 'string "010"' => ['010', false]; // PHP's filter_var doesn't parse octal
        yield 'string "0755"' => ['0755', false]; // PHP's filter_var doesn't parse octal

        // Hexadecimal strings (filter_var doesn't handle hex)
        yield 'string "0x10"' => ['0x10', false]; // PHP's filter_var doesn't parse hex
        yield 'string "0xFF"' => ['0xFF', false]; // PHP's filter_var doesn't parse hex
        yield 'string "0xFFFFFFFF"' => ['0xFFFFFFFF', false]; // PHP's filter_var doesn't parse hex

        // String representations of floats - should return false
        yield 'string "3.14"' => ['3.14', false];
        yield 'string "42.0"' => ['42.0', false]; // Has decimal point
        yield 'string "1e10"' => ['1e10', false]; // Scientific notation
        yield 'string "1.23e10"' => ['1.23e10', false];

        // Special float values
        yield 'float INF' => [\INF, false];
        yield 'float -INF' => [-\INF, false];
        yield 'float NAN' => [\NAN, false];

        // Invalid types
        yield 'null' => [null, false];
        yield 'bool true' => [true, false];
        yield 'bool false' => [false, false];
        yield 'array' => [[1, 2, 3], false];
        yield 'empty array' => [[], false];
        yield 'object stdClass' => [new \stdClass(), false];

        // Invalid strings
        yield 'string "abc"' => ['abc', false];
        yield 'string "12.34.56"' => ['12.34.56', false];
        yield 'string empty' => ['', false];
        yield 'string with spaces "  42  "' => ['  42  ', true]; // Trimmed strings are valid
        yield 'string "42abc"' => ['42abc', false];

        // Stringable objects
        yield 'stringable returning "42"' => [new class
        {
            public function __toString(): string
            {
                return '42';
            }
        }, true];
        yield 'stringable returning "3.14"' => [new class
        {
            public function __toString(): string
            {
                return '3.14';
            }
        }, false];
        yield 'stringable returning "invalid"' => [new class
        {
            public function __toString(): string
            {
                return 'invalid';
            }
        }, false];

        // String representations beyond int range
        yield 'string beyond PHP_INT_MAX' => ['99999999999999999999999999', false];
        yield 'string beyond PHP_INT_MIN' => ['-99999999999999999999999999', false];
    }

    #[Test]
    #[DataProvider('representAsFloatProvider')]
    public function representAsFloatReturnsExpectedResult(mixed $value, float|null $expected): void
    {
        $result = represent_as_float($value);
        if ($expected === null) {
            self::assertNull($result);
        } elseif (\is_nan($expected)) {
            self::assertIsFloat($result);
            self::assertNan($result);
        } else {
            self::assertSame($expected, $result);
        }
    }

    public static function representAsFloatProvider(): \Iterator
    {
        // Basic floats - should return the same value
        yield 'float 0.0' => [0.0, 0.0];
        yield 'float 3.14' => [3.14, 3.14];
        yield 'float -3.14' => [-3.14, -3.14];

        // Integers that can be represented as float
        yield 'int 0' => [0, 0.0];
        yield 'int 42' => [42, 42.0];
        yield 'int -42' => [-42, -42.0];
        yield 'int 2^53' => [9007199254740992, 9007199254740992.0];

        // String representations
        yield 'string "3.14"' => ['3.14', 3.14];
        yield 'string "42"' => ['42', 42.0];
        yield 'string "1e10"' => ['1e10', 10000000000.0];

        // Special values
        yield 'float INF' => [\INF, \INF];
        yield 'float -INF' => [-\INF, -\INF];
        yield 'float NAN' => [\NAN, \NAN];
        yield 'string "INF"' => ['INF', \INF];
        yield 'string "-INF"' => ['-INF', -\INF];
        yield 'string "NAN"' => ['NAN', \NAN];

        // Values that cannot be represented
        yield 'int 2^53 + 1' => [9007199254740993, null];
        yield 'null' => [null, null];
        yield 'bool true' => [true, null];
        yield 'array' => [[1, 2, 3], null];
        yield 'string "abc"' => ['abc', null];

        // Stringable objects
        yield 'stringable returning "42.5"' => [new class
        {
            public function __toString(): string
            {
                return '42.5';
            }
        }, 42.5];
        yield 'stringable returning "invalid"' => [new class
        {
            public function __toString(): string
            {
                return 'invalid';
            }
        }, null];
    }

    #[Test]
    #[DataProvider('representAsIntProvider')]
    public function representAsIntReturnsExpectedResult(mixed $value, int|null $expected): void
    {
        self::assertSame($expected, represent_as_int($value));
    }

    public static function representAsIntProvider(): \Iterator
    {
        // Basic integers - should return the same value
        yield 'int 0' => [0, 0];
        yield 'int 42' => [42, 42];
        yield 'int -42' => [-42, -42];
        yield 'int PHP_INT_MAX' => [\PHP_INT_MAX, \PHP_INT_MAX];
        yield 'int PHP_INT_MIN' => [\PHP_INT_MIN, \PHP_INT_MIN];

        // Floats without fractional parts
        yield 'float 0.0' => [0.0, 0];
        yield 'float 42.0' => [42.0, 42];
        yield 'float -42.0' => [-42.0, -42];

        // String representations
        yield 'string "0"' => ['0', 0];
        yield 'string "42"' => ['42', 42];
        yield 'string "-42"' => ['-42', -42];
        yield 'string "010"' => ['010', null]; // filter_var doesn't handle octal
        yield 'string "0x10"' => ['0x10', null]; // filter_var doesn't handle hex
        yield 'string "0xFF"' => ['0xFF', null]; // filter_var doesn't handle hex

        // Values that cannot be represented
        yield 'float 3.14' => [3.14, null];
        yield 'float INF' => [\INF, null];
        yield 'string "3.14"' => ['3.14', null];
        yield 'string "abc"' => ['abc', null];
        yield 'null' => [null, null];
        yield 'bool true' => [true, null];
        yield 'array' => [[1, 2, 3], null];

        // Stringable objects
        yield 'stringable returning "42"' => [new class
        {
            public function __toString(): string
            {
                return '42';
            }
        }, 42];
        yield 'stringable returning "3.14"' => [new class
        {
            public function __toString(): string
            {
                return '3.14';
            }
        }, null];
    }

    #[Test]
    public function representAsFloatAndIntConsistentWithIsRepresentable(): void
    {
        // Test a variety of values to ensure consistency
        $test_values = [
            0, 42, -42, 3.14, -3.14, '42', '3.14', 'abc', null, true, [],
            \INF, -\INF, \NAN, 9007199254740992, 9007199254740993,
            new class
            {
                public function __toString(): string
                {
                    return '42';
                }
            },
        ];

        foreach ($test_values as $value) {
            if (is_representable_as_float($value)) {
                self::assertNotNull(represent_as_float($value), 'represent_as_float should not return null when is_representable_as_float returns true');
            } else {
                self::assertNull(represent_as_float($value), 'represent_as_float should return null when is_representable_as_float returns false');
            }

            if (is_representable_as_int($value)) {
                self::assertNotNull(represent_as_int($value), 'represent_as_int should not return null when is_representable_as_int returns true');
            } else {
                self::assertNull(represent_as_int($value), 'represent_as_int should return null when is_representable_as_int returns false');
            }
        }
    }
}
