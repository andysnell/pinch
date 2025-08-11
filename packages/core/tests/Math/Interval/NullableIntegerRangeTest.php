<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math\Interval;

use PhoneBurner\Pinch\Math\Interval\NullableIntegerRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

final class NullableIntegerRangeTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRangeWithNonNullValues(): void
    {
        $range = new NullableIntegerRange(1, 10);

        self::assertSame(1, $range->min());
        self::assertSame(10, $range->max());
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithNullMin(): void
    {
        $range = new NullableIntegerRange(null, 10);

        self::assertNull($range->min());
        self::assertSame(10, $range->max());
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithNullMax(): void
    {
        $range = new NullableIntegerRange(1, null);

        self::assertSame(1, $range->min());
        self::assertNull($range->max());
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithBothNull(): void
    {
        $range = new NullableIntegerRange(null, null);

        self::assertNull($range->min());
        self::assertNull($range->max());
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorAllowsEqualMinAndMax(): void
    {
        $range = new NullableIntegerRange(5, 5);

        self::assertSame(5, $range->min());
        self::assertSame(5, $range->max());
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorThrowsExceptionWhenMaxIsLessThanMin(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');

        new NullableIntegerRange(10, 1);
    }

    #[Test]
    public function constructorAcceptsCustomBoundary(): void
    {
        $range = new NullableIntegerRange(1, 10, IntervalBoundary::OpenOpen);

        self::assertSame(IntervalBoundary::OpenOpen, $range->boundary);
    }

    #[Test]
    #[DataProvider('boundaryProvider')]
    public function constructorAcceptsAllBoundaryTypes(IntervalBoundary $boundary): void
    {
        $range = new NullableIntegerRange(1, 10, $boundary);

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
    public function unboundedReturnsCorrectValue(int|null $min, int|null $max, bool $expected): void
    {
        $range = new NullableIntegerRange($min, $max);

        self::assertSame($expected, $range->unbounded());
    }

    public static function unboundedProvider(): \Iterator
    {
        yield 'both non-null' => [1, 10, false];
        yield 'null min' => [null, 10, true];
        yield 'null max' => [1, null, true];
        yield 'both null' => [null, null, true];
        yield 'zero values' => [0, 0, false];
        yield 'negative values' => [-10, -1, false];
        yield 'negative to positive' => [-5, 5, false];
    }

    #[Test]
    public function minReturnsCorrectValue(): void
    {
        $range1 = new NullableIntegerRange(42, 100);
        self::assertSame(42, $range1->min());

        $range2 = new NullableIntegerRange(null, 100);
        self::assertNull($range2->min());

        $range3 = new NullableIntegerRange(-50, 0);
        self::assertSame(-50, $range3->min());
    }

    #[Test]
    public function maxReturnsCorrectValue(): void
    {
        $range1 = new NullableIntegerRange(1, 99);
        self::assertSame(99, $range1->max());

        $range2 = new NullableIntegerRange(1, null);
        self::assertNull($range2->max());

        $range3 = new NullableIntegerRange(-100, -10);
        self::assertSame(-10, $range3->max());
    }

    #[Test]
    public function publicPropertiesAreReadonly(): void
    {
        $range = new NullableIntegerRange(1, 10);

        self::assertSame(1, $range->min);
        self::assertSame(10, $range->max);
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
    }

    #[Test]
    #[DataProvider('edgeCaseProvider')]
    public function handlesEdgeCasesCorrectly(int|null $min, int|null $max): void
    {
        $range = new NullableIntegerRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function edgeCaseProvider(): \Iterator
    {
        yield 'PHP_INT_MIN to PHP_INT_MAX' => [\PHP_INT_MIN, \PHP_INT_MAX];
        yield 'null to PHP_INT_MAX' => [null, \PHP_INT_MAX];
        yield 'PHP_INT_MIN to null' => [\PHP_INT_MIN, null];
        yield 'zero to zero' => [0, 0];
        yield 'negative one to one' => [-1, 1];
        yield 'large negative to zero' => [-1000000, 0];
        yield 'zero to large positive' => [0, 1000000];
    }
}
