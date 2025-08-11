<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math\Interval;

use PhoneBurner\Pinch\Math\Interval\IntegerRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

final class IntegerRangeTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRange(): void
    {
        $range = new IntegerRange(1, 10);

        self::assertSame(1, $range->min());
        self::assertSame(10, $range->max());
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
        self::assertFalse($range->unbounded());
        self::assertSame(1, $range->min);
        self::assertSame(10, $range->max);
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
    }

    #[Test]
    public function constructorAllowsEqualMinAndMax(): void
    {
        $range = new IntegerRange(5, 5);

        self::assertSame(5, $range->min());
        self::assertSame(5, $range->max());
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorThrowsExceptionWhenMaxIsLessThanMin(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');

        new IntegerRange(10, 1);
    }

    #[Test]
    public function constructorAcceptsCustomBoundary(): void
    {
        $range = new IntegerRange(1, 10, IntervalBoundary::OpenOpen);

        self::assertSame(IntervalBoundary::OpenOpen, $range->boundary);
    }

    #[Test]
    #[DataProvider('boundaryProvider')]
    public function constructorAcceptsAllBoundaryTypes(IntervalBoundary $boundary): void
    {
        $range = new IntegerRange(1, 10, $boundary);

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
        $range = new IntegerRange(1, 10);
        self::assertFalse($range->unbounded());

        $range2 = new IntegerRange(\PHP_INT_MIN, \PHP_INT_MAX);
        self::assertFalse($range2->unbounded());
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function containsWorksCorrectly(
        int $min,
        int $max,
        IntervalBoundary $boundary,
        int|float $value,
        bool $expected,
    ): void {
        $range = new IntegerRange($min, $max, $boundary);

        self::assertSame($expected, $range->contains($value));
    }

    public static function containsProvider(): \Iterator
    {
        yield 'ClosedClosed includes min' => [1, 10, IntervalBoundary::ClosedClosed, 1, true];
        yield 'ClosedClosed includes max' => [1, 10, IntervalBoundary::ClosedClosed, 10, true];
        yield 'ClosedClosed includes middle' => [1, 10, IntervalBoundary::ClosedClosed, 5, true];
        yield 'ClosedClosed excludes below' => [1, 10, IntervalBoundary::ClosedClosed, 0, false];
        yield 'ClosedClosed excludes above' => [1, 10, IntervalBoundary::ClosedClosed, 11, false];

        yield 'OpenOpen excludes min' => [1, 10, IntervalBoundary::OpenOpen, 1, false];
        yield 'OpenOpen excludes max' => [1, 10, IntervalBoundary::OpenOpen, 10, false];
        yield 'OpenOpen includes middle' => [1, 10, IntervalBoundary::OpenOpen, 5, true];
        yield 'OpenOpen excludes below' => [1, 10, IntervalBoundary::OpenOpen, 0, false];
        yield 'OpenOpen excludes above' => [1, 10, IntervalBoundary::OpenOpen, 11, false];

        yield 'OpenClosed excludes min' => [1, 10, IntervalBoundary::OpenClosed, 1, false];
        yield 'OpenClosed includes max' => [1, 10, IntervalBoundary::OpenClosed, 10, true];
        yield 'OpenClosed includes middle' => [1, 10, IntervalBoundary::OpenClosed, 5, true];

        yield 'ClosedOpen includes min' => [1, 10, IntervalBoundary::ClosedOpen, 1, true];
        yield 'ClosedOpen excludes max' => [1, 10, IntervalBoundary::ClosedOpen, 10, false];
        yield 'ClosedOpen includes middle' => [1, 10, IntervalBoundary::ClosedOpen, 5, true];

        yield 'float value in int range' => [1, 10, IntervalBoundary::ClosedClosed, 5.5, true];
        yield 'float value at boundary' => [1, 10, IntervalBoundary::ClosedClosed, 1.0, true];
        yield 'negative range' => [-10, -1, IntervalBoundary::ClosedClosed, -5, true];
    }

    #[Test]
    public function includesMinimumWorksCorrectly(): void
    {
        $closedClosed = new IntegerRange(1, 10, IntervalBoundary::ClosedClosed);
        self::assertTrue($closedClosed->includesMinimum());

        $closedOpen = new IntegerRange(1, 10, IntervalBoundary::ClosedOpen);
        self::assertTrue($closedOpen->includesMinimum());

        $openClosed = new IntegerRange(1, 10, IntervalBoundary::OpenClosed);
        self::assertFalse($openClosed->includesMinimum());

        $openOpen = new IntegerRange(1, 10, IntervalBoundary::OpenOpen);
        self::assertFalse($openOpen->includesMinimum());
    }

    #[Test]
    public function includesMaximumWorksCorrectly(): void
    {
        $closedClosed = new IntegerRange(1, 10, IntervalBoundary::ClosedClosed);
        self::assertTrue($closedClosed->includesMaximum());

        $closedOpen = new IntegerRange(1, 10, IntervalBoundary::ClosedOpen);
        self::assertFalse($closedOpen->includesMaximum());

        $openClosed = new IntegerRange(1, 10, IntervalBoundary::OpenClosed);
        self::assertTrue($openClosed->includesMaximum());

        $openOpen = new IntegerRange(1, 10, IntervalBoundary::OpenOpen);
        self::assertFalse($openOpen->includesMaximum());
    }

    #[Test]
    #[DataProvider('iteratorProvider')]
    public function getIteratorReturnsCorrectValues(
        int $min,
        int $max,
        IntervalBoundary $boundary,
        array $expected,
    ): void {
        $range = new IntegerRange($min, $max, $boundary);
        $actual = \iterator_to_array($range->getIterator());

        self::assertSame($expected, \array_values($actual));
    }

    public static function iteratorProvider(): \Iterator
    {
        yield 'ClosedClosed 1-5' => [1, 5, IntervalBoundary::ClosedClosed, [1, 2, 3, 4, 5]];
        yield 'OpenOpen 1-5' => [1, 5, IntervalBoundary::OpenOpen, [2, 3, 4]];
        yield 'OpenClosed 1-5' => [1, 5, IntervalBoundary::OpenClosed, [2, 3, 4, 5]];
        yield 'ClosedOpen 1-5' => [1, 5, IntervalBoundary::ClosedOpen, [1, 2, 3, 4]];

        yield 'single value ClosedClosed' => [5, 5, IntervalBoundary::ClosedClosed, [5]];
        yield 'single value OpenOpen' => [5, 5, IntervalBoundary::OpenOpen, []];

        yield 'negative range' => [-3, -1, IntervalBoundary::ClosedClosed, [-3, -2, -1]];
        yield 'zero crossing' => [-2, 2, IntervalBoundary::ClosedClosed, [-2, -1, 0, 1, 2]];

        yield 'adjacent values ClosedClosed' => [1, 2, IntervalBoundary::ClosedClosed, [1, 2]];
        yield 'adjacent values OpenOpen' => [1, 2, IntervalBoundary::OpenOpen, []];
    }

    #[Test]
    public function implementsIteratorAggregate(): void
    {
        $range = new IntegerRange(1, 10);
        $values = [];
        foreach ($range as $value) {
            $values[] = $value;
        }

        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $values);
    }

    #[Test]
    #[DataProvider('providesCountableTests')]
    public function implementsCountable(int $expected, IntegerRange $range): void
    {
        self::assertCount($expected, $range);
    }

    public static function providesCountableTests(): \Generator
    {
        yield [10, new IntegerRange(1, 10)];
        yield [10, new IntegerRange(-10, -1)];
        yield [0, new IntegerRange(5, 5, IntervalBoundary::OpenOpen)];
        yield [1, new IntegerRange(5, 5, IntervalBoundary::OpenClosed)];
        yield [1, new IntegerRange(5, 5, IntervalBoundary::ClosedOpen)];
        yield [1, new IntegerRange(5, 5, IntervalBoundary::ClosedClosed)];
        yield [5, new IntegerRange(1, 5, IntervalBoundary::ClosedClosed)];
        yield [3, new IntegerRange(1, 5, IntervalBoundary::OpenOpen)];
        yield [4, new IntegerRange(1, 5, IntervalBoundary::OpenClosed)];
        yield [4, new IntegerRange(1, 5, IntervalBoundary::ClosedOpen)];
        yield [5, new IntegerRange(0, 4, IntervalBoundary::ClosedClosed)];
        yield [5, new IntegerRange(-1, 3, IntervalBoundary::ClosedClosed)];
        yield [5, new IntegerRange(-2, 2, IntervalBoundary::ClosedClosed)];
    }

    #[Test]
    #[DataProvider('edgeCaseProvider')]
    public function handlesEdgeCasesCorrectly(int $min, int $max): void
    {
        $range = new IntegerRange($min, $max);

        self::assertSame($min, $range->min());
        self::assertSame($max, $range->max());
    }

    public static function edgeCaseProvider(): \Iterator
    {
        yield 'PHP_INT_MIN to PHP_INT_MAX' => [\PHP_INT_MIN, \PHP_INT_MAX];
        yield 'zero to zero' => [0, 0];
        yield 'negative one to one' => [-1, 1];
        yield 'large negative to zero' => [-1000000, 0];
        yield 'zero to large positive' => [0, 1000000];
    }
}
