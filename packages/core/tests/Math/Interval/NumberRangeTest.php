<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math\Interval;

use PhoneBurner\Pinch\Math\Interval\NumberRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

final class NumberRangeTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRange(): void
    {
        $range = new NumberRange(1.5, 10.5);

        self::assertSame(1.5, $range->min());
        self::assertSame(10.5, $range->max());
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorAllowsEqualMinAndMax(): void
    {
        $range = new NumberRange(5.5, 5.5);

        self::assertSame(5.5, $range->min());
        self::assertSame(5.5, $range->max());
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorThrowsExceptionWhenMaxIsLessThanMin(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');

        new NumberRange(10.5, 1.5);
    }

    #[Test]
    public function constructorAcceptsCustomBoundary(): void
    {
        $range = new NumberRange(1.0, 10.0, IntervalBoundary::OpenOpen);

        self::assertSame(IntervalBoundary::OpenOpen, $range->boundary);
    }

    #[Test]
    #[DataProvider('boundaryProvider')]
    public function constructorAcceptsAllBoundaryTypes(IntervalBoundary $boundary): void
    {
        $range = new NumberRange(1.0, 10.0, $boundary);

        self::assertSame($boundary, $range->boundary);
    }

    public static function boundaryProvider(): \Iterator
    {
        yield 'ClosedClosed' => [IntervalBoundary::ClosedClosed];
        yield 'ClosedOpen' => [IntervalBoundary::ClosedOpen];
        yield 'OpenClosed' => [IntervalBoundary::OpenClosed];
        yield 'OpenOpen' => [IntervalBoundary::OpenOpen];
    }

    #[Test]
    public function unboundedAlwaysReturnsFalse(): void
    {
        $range = new NumberRange(1.5, 10.5);
        self::assertFalse($range->unbounded());

        $range2 = new NumberRange(\PHP_FLOAT_MIN, \PHP_FLOAT_MAX);
        self::assertFalse($range2->unbounded());

        $range3 = new NumberRange(\PHP_INT_MIN, \PHP_INT_MAX);
        self::assertFalse($range3->unbounded());
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function containsWorksCorrectly(
        int|float $min,
        int|float $max,
        IntervalBoundary $boundary,
        int|float $value,
        bool $expected,
    ): void {
        $range = new NumberRange($min, $max, $boundary);

        self::assertSame($expected, $range->contains($value));
    }

    public static function containsProvider(): \Iterator
    {
        yield 'ClosedClosed includes min' => [1.0, 10.0, IntervalBoundary::ClosedClosed, 1.0, true];
        yield 'ClosedClosed includes max' => [1.0, 10.0, IntervalBoundary::ClosedClosed, 10.0, true];
        yield 'ClosedClosed includes middle' => [1.0, 10.0, IntervalBoundary::ClosedClosed, 5.5, true];
        yield 'ClosedClosed excludes below' => [1.0, 10.0, IntervalBoundary::ClosedClosed, 0.9, false];
        yield 'ClosedClosed excludes above' => [1.0, 10.0, IntervalBoundary::ClosedClosed, 10.1, false];

        yield 'OpenOpen excludes min' => [1.0, 10.0, IntervalBoundary::OpenOpen, 1.0, false];
        yield 'OpenOpen excludes max' => [1.0, 10.0, IntervalBoundary::OpenOpen, 10.0, false];
        yield 'OpenOpen includes middle' => [1.0, 10.0, IntervalBoundary::OpenOpen, 5.5, true];
        yield 'OpenOpen excludes below' => [1.0, 10.0, IntervalBoundary::OpenOpen, 0.9, false];
        yield 'OpenOpen excludes above' => [1.0, 10.0, IntervalBoundary::OpenOpen, 10.1, false];

        yield 'OpenClosed excludes min' => [1.0, 10.0, IntervalBoundary::OpenClosed, 1.0, false];
        yield 'OpenClosed includes max' => [1.0, 10.0, IntervalBoundary::OpenClosed, 10.0, true];
        yield 'OpenClosed includes middle' => [1.0, 10.0, IntervalBoundary::OpenClosed, 5.5, true];

        yield 'ClosedOpen includes min' => [1.0, 10.0, IntervalBoundary::ClosedOpen, 1.0, true];
        yield 'ClosedOpen excludes max' => [1.0, 10.0, IntervalBoundary::ClosedOpen, 10.0, false];
        yield 'ClosedOpen includes middle' => [1.0, 10.0, IntervalBoundary::ClosedOpen, 5.5, true];

        yield 'integer values in float range' => [1.5, 10.5, IntervalBoundary::ClosedClosed, 5, true];
        yield 'integer min and max' => [1, 10, IntervalBoundary::ClosedClosed, 5, true];
        yield 'mixed int and float' => [1, 10.5, IntervalBoundary::ClosedClosed, 5.25, true];
        yield 'negative range' => [-10.5, -1.5, IntervalBoundary::ClosedClosed, -5.0, true];
        yield 'zero crossing' => [-5.5, 5.5, IntervalBoundary::ClosedClosed, 0.0, true];

        yield 'precision boundary check' => [1.0, 2.0, IntervalBoundary::ClosedClosed, 1.0000000001, true];
        yield 'precision outside range' => [1.0, 2.0, IntervalBoundary::ClosedClosed, 0.9999999999, false];
    }

    #[Test]
    #[DataProvider('numberTypeProvider')]
    public function supportsIntegersAndFloats(int|float $min, int|float $max): void
    {
        $range = new NumberRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function numberTypeProvider(): \Iterator
    {
        yield 'integers' => [1, 10];
        yield 'floats' => [1.5, 10.5];
        yield 'int min, float max' => [1, 10.5];
        yield 'float min, int max' => [1.5, 10];
        yield 'negative integers' => [-10, -1];
        yield 'negative floats' => [-10.5, -1.5];
        yield 'scientific notation' => [1.23e-4, 1.23e4];
        yield 'very small floats' => [1e-308, 1e-307];
        yield 'very large floats' => [1e307, 1e308];
    }

    #[Test]
    public function publicPropertiesAreReadonly(): void
    {
        $range = new NumberRange(1.5, 10.5);

        self::assertSame(1.5, $range->min);
        self::assertSame(10.5, $range->max);
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
    }

    #[Test]
    #[DataProvider('edgeCaseProvider')]
    public function handlesEdgeCasesCorrectly(int|float $min, int|float $max): void
    {
        $range = new NumberRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function edgeCaseProvider(): \Iterator
    {
        yield 'PHP_INT_MIN to PHP_INT_MAX' => [\PHP_INT_MIN, \PHP_INT_MAX];
        yield 'PHP_FLOAT_MIN to PHP_FLOAT_MAX' => [\PHP_FLOAT_MIN, \PHP_FLOAT_MAX];
        yield 'negative infinity to positive infinity' => [-\INF, \INF];
        yield 'zero to zero' => [0, 0];
        yield 'zero float to zero float' => [0.0, 0.0];
        yield 'negative one to one' => [-1.0, 1.0];
        yield 'mixed very small and large' => [1e-100, 1e100];
    }

    #[Test]
    #[DataProvider('precisionProvider')]
    public function maintainsFloatPrecision(float $min, float $max): void
    {
        $range = new NumberRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function precisionProvider(): \Iterator
    {
        yield 'high precision decimals' => [0.123456789, 0.987654321];
        yield 'pi range' => [3.14159265358979, 3.14159265358980];
        yield 'euler range' => [2.71828182845904, 2.71828182845905];
        yield 'small differences' => [1.0000001, 1.0000002];
        yield 'negative precision' => [-0.123456789, -0.123456788];
    }

    #[Test]
    #[DataProvider('containsPrecisionProvider')]
    public function containsHandlesPrecisionCorrectly(
        float $min,
        float $max,
        float $value,
        bool $expected,
    ): void {
        $range = new NumberRange($min, $max);

        self::assertSame($expected, $range->contains($value));
    }

    public static function containsPrecisionProvider(): \Iterator
    {
        yield 'value at exact min' => [1.123456789, 2.0, 1.123456789, true];
        yield 'value at exact max' => [1.0, 2.123456789, 2.123456789, true];
        yield 'value just inside min' => [1.0, 2.0, 1.0000000001, true];
        yield 'value just inside max' => [1.0, 2.0, 1.9999999999, true];
        yield 'value just outside min' => [1.0, 2.0, 0.9999999999, false];
        yield 'value just outside max' => [1.0, 2.0, 2.0000000001, false];
    }
}
