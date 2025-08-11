<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math\Interval;

use PhoneBurner\Pinch\Math\Interval\NullableNumberRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

final class NullableNumberRangeTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRangeWithNonNullValues(): void
    {
        $range = new NullableNumberRange(1.5, 10.5);

        self::assertSame(1.5, $range->min());
        self::assertSame(10.5, $range->max());
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithNullMin(): void
    {
        $range = new NullableNumberRange(null, 10.5);

        self::assertNull($range->min());
        self::assertSame(10.5, $range->max());
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithNullMax(): void
    {
        $range = new NullableNumberRange(1.5, null);

        self::assertSame(1.5, $range->min());
        self::assertNull($range->max());
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithBothNull(): void
    {
        $range = new NullableNumberRange(null, null);

        self::assertNull($range->min());
        self::assertNull($range->max());
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorAllowsEqualMinAndMax(): void
    {
        $range = new NullableNumberRange(5.5, 5.5);

        self::assertSame(5.5, $range->min());
        self::assertSame(5.5, $range->max());
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorThrowsExceptionWhenMaxIsLessThanMin(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');

        new NullableNumberRange(10.5, 1.5);
    }

    #[Test]
    public function constructorAcceptsCustomBoundary(): void
    {
        $range = new NullableNumberRange(1.0, 10.0, IntervalBoundary::OpenOpen);

        self::assertSame(IntervalBoundary::OpenOpen, $range->boundary);
    }

    #[Test]
    #[DataProvider('boundaryProvider')]
    public function constructorAcceptsAllBoundaryTypes(IntervalBoundary $boundary): void
    {
        $range = new NullableNumberRange(1.0, 10.0, $boundary);

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
    #[DataProvider('unboundedProvider')]
    public function unboundedReturnsCorrectValue(int|float|null $min, int|float|null $max, bool $expected): void
    {
        $range = new NullableNumberRange($min, $max);

        self::assertSame($expected, $range->unbounded());
    }

    public static function unboundedProvider(): \Iterator
    {
        yield 'both non-null integers' => [1, 10, false];
        yield 'both non-null floats' => [1.5, 10.5, false];
        yield 'mixed int and float' => [1, 10.5, false];
        yield 'null min with int' => [null, 10, true];
        yield 'null min with float' => [null, 10.5, true];
        yield 'null max with int' => [1, null, true];
        yield 'null max with float' => [1.5, null, true];
        yield 'both null' => [null, null, true];
        yield 'zero values' => [0, 0, false];
        yield 'zero float values' => [0.0, 0.0, false];
        yield 'negative values' => [-10.5, -1.5, false];
        yield 'negative to positive' => [-5.5, 5.5, false];
    }

    #[Test]
    #[DataProvider('numberTypeProvider')]
    public function supportsIntegersAndFloats(int|float|null $min, int|float|null $max): void
    {
        $range = new NullableNumberRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function numberTypeProvider(): \Iterator
    {
        yield 'integers' => [1, 10];
        yield 'floats' => [1.5, 10.5];
        yield 'int min, float max' => [1, 10.5];
        yield 'float min, int max' => [1.5, 10];
        yield 'null min, int max' => [null, 10];
        yield 'null min, float max' => [null, 10.5];
        yield 'int min, null max' => [1, null];
        yield 'float min, null max' => [1.5, null];
        yield 'negative integers' => [-10, -1];
        yield 'negative floats' => [-10.5, -1.5];
        yield 'scientific notation' => [1.23e-4, 1.23e4];
    }

    #[Test]
    public function minReturnsCorrectValue(): void
    {
        $range1 = new NullableNumberRange(42.5, 100.5);
        self::assertSame(42.5, $range1->min());

        $range2 = new NullableNumberRange(null, 100.5);
        self::assertNull($range2->min());

        $range3 = new NullableNumberRange(-50, 0);
        self::assertSame(-50, $range3->min());

        $range4 = new NullableNumberRange(42, 100);
        self::assertSame(42, $range4->min());
    }

    #[Test]
    public function maxReturnsCorrectValue(): void
    {
        $range1 = new NullableNumberRange(1.5, 99.5);
        self::assertSame(99.5, $range1->max());

        $range2 = new NullableNumberRange(1.5, null);
        self::assertNull($range2->max());

        $range3 = new NullableNumberRange(-100.5, -10.5);
        self::assertSame(-10.5, $range3->max());

        $range4 = new NullableNumberRange(1, 99);
        self::assertSame(99, $range4->max());
    }

    #[Test]
    public function publicPropertiesAreReadonly(): void
    {
        $range = new NullableNumberRange(1.5, 10.5);

        self::assertSame(1.5, $range->min);
        self::assertSame(10.5, $range->max);
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
    }

    #[Test]
    #[DataProvider('edgeCaseProvider')]
    public function handlesEdgeCasesCorrectly(int|float|null $min, int|float|null $max): void
    {
        $range = new NullableNumberRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function edgeCaseProvider(): \Iterator
    {
        yield 'PHP_INT_MIN to PHP_INT_MAX' => [\PHP_INT_MIN, \PHP_INT_MAX];
        yield 'null to PHP_INT_MAX' => [null, \PHP_INT_MAX];
        yield 'PHP_INT_MIN to null' => [\PHP_INT_MIN, null];
        yield 'PHP_FLOAT_MIN to PHP_FLOAT_MAX' => [\PHP_FLOAT_MIN, \PHP_FLOAT_MAX];
        yield 'negative infinity to positive infinity' => [-\INF, \INF];
        yield 'zero to zero' => [0, 0];
        yield 'zero float to zero float' => [0.0, 0.0];
        yield 'negative one to one' => [-1.0, 1.0];
        yield 'very small floats' => [1e-308, 1e-307];
        yield 'very large floats' => [1e307, 1e308];
        yield 'mixed very small and large' => [1e-100, 1e100];
    }

    #[Test]
    #[DataProvider('precisionProvider')]
    public function maintainsFloatPrecision(float $min, float $max): void
    {
        $range = new NullableNumberRange($min, $max);

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
}
