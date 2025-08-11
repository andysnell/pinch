<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Interval;

use PhoneBurner\Pinch\Time\Interval\NullableDateTimeImmutableRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

final class NullableDateTimeImmutableRangeTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRangeWithNonNullValues(): void
    {
        $start = new \DateTimeImmutable('2024-01-01 00:00:00');
        $end = new \DateTimeImmutable('2024-12-31 23:59:59');
        $range = new NullableDateTimeImmutableRange($start, $end);

        self::assertEquals($start, $range->min());
        self::assertEquals($end, $range->max());
        self::assertEquals($start, $range->start);
        self::assertEquals($end, $range->end);
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithNullStart(): void
    {
        $end = new \DateTimeImmutable('2024-12-31 23:59:59');
        $range = new NullableDateTimeImmutableRange(null, $end);

        self::assertNull($range->min());
        self::assertEquals($end, $range->max());
        self::assertNull($range->start);
        self::assertEquals($end, $range->end);
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithNullEnd(): void
    {
        $start = new \DateTimeImmutable('2024-01-01 00:00:00');
        $range = new NullableDateTimeImmutableRange($start, null);

        self::assertEquals($start, $range->min());
        self::assertNull($range->max());
        self::assertEquals($start, $range->start);
        self::assertNull($range->end);
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorCreatesValidRangeWithBothNull(): void
    {
        $range = new NullableDateTimeImmutableRange(null, null);

        self::assertNull($range->min());
        self::assertNull($range->max());
        self::assertNull($range->start);
        self::assertNull($range->end);
        self::assertTrue($range->unbounded());
    }

    #[Test]
    public function constructorConvertsDateTimeToDateTimeImmutable(): void
    {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-01-01 00:00:00') ?: throw new \RuntimeException();
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-31 23:59:59') ?: throw new \RuntimeException();
        $range = new NullableDateTimeImmutableRange($start, $end);

        self::assertInstanceOf(\DateTimeImmutable::class, $range->min());
        self::assertInstanceOf(\DateTimeImmutable::class, $range->max());
        self::assertEquals($start, $range->min());
        self::assertEquals($end, $range->max());
    }

    #[Test]
    public function constructorAllowsEqualStartAndEnd(): void
    {
        $date = new \DateTimeImmutable('2024-06-15 12:00:00');
        $range = new NullableDateTimeImmutableRange($date, $date);

        self::assertEquals($date, $range->min());
        self::assertEquals($date, $range->max());
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorThrowsExceptionWhenEndIsBeforeStart(): void
    {
        $start = new \DateTimeImmutable('2024-12-31 23:59:59');
        $end = new \DateTimeImmutable('2024-01-01 00:00:00');

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max must be greater than or equal to min');

        new NullableDateTimeImmutableRange($start, $end);
    }

    #[Test]
    public function constructorAcceptsCustomBoundary(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new NullableDateTimeImmutableRange($start, $end, IntervalBoundary::OpenOpen);

        self::assertSame(IntervalBoundary::OpenOpen, $range->boundary);
    }

    #[Test]
    #[DataProvider('boundaryProvider')]
    public function constructorAcceptsAllBoundaryTypes(IntervalBoundary $boundary): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new NullableDateTimeImmutableRange($start, $end, $boundary);

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
    public function unboundedReturnsCorrectValue(
        \DateTimeInterface|null $start,
        \DateTimeInterface|null $end,
        bool $expected,
    ): void {
        $range = new NullableDateTimeImmutableRange($start, $end);

        self::assertSame($expected, $range->unbounded());
    }

    public static function unboundedProvider(): \Iterator
    {
        $date1 = new \DateTimeImmutable('2024-01-01');
        $date2 = new \DateTimeImmutable('2024-12-31');

        yield 'both non-null' => [$date1, $date2, false];
        yield 'null start' => [null, $date2, true];
        yield 'null end' => [$date1, null, true];
        yield 'both null' => [null, null, true];
        yield 'equal dates' => [$date1, $date1, false];
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function containsWorksCorrectly(
        \DateTimeInterface|null $start,
        \DateTimeInterface|null $end,
        IntervalBoundary $boundary,
        string $checkDate,
        bool $expected,
    ): void {
        $range = new NullableDateTimeImmutableRange($start, $end, $boundary);

        self::assertSame($expected, $range->contains(new \DateTimeImmutable($checkDate)));
    }

    public static function containsProvider(): \Iterator
    {
        $start = new \DateTimeImmutable('2024-01-01 00:00:00');
        $end = new \DateTimeImmutable('2024-12-31 23:59:59');
        $middle = '2024-06-15 12:00:00';
        $before = '2023-12-31 23:59:59';
        $after = '2025-01-01 00:00:00';

        // Regular bounded range tests
        yield 'ClosedClosed includes start' => [$start, $end, IntervalBoundary::ClosedClosed, '2024-01-01 00:00:00', true];
        yield 'ClosedClosed includes end' => [$start, $end, IntervalBoundary::ClosedClosed, '2024-12-31 23:59:59', true];
        yield 'ClosedClosed includes middle' => [$start, $end, IntervalBoundary::ClosedClosed, $middle, true];
        yield 'ClosedClosed excludes before' => [$start, $end, IntervalBoundary::ClosedClosed, $before, false];
        yield 'ClosedClosed excludes after' => [$start, $end, IntervalBoundary::ClosedClosed, $after, false];

        yield 'OpenOpen excludes start' => [$start, $end, IntervalBoundary::OpenOpen, '2024-01-01 00:00:00', false];
        yield 'OpenOpen excludes end' => [$start, $end, IntervalBoundary::OpenOpen, '2024-12-31 23:59:59', false];
        yield 'OpenOpen includes middle' => [$start, $end, IntervalBoundary::OpenOpen, $middle, true];

        yield 'null start contains before' => [null, $end, IntervalBoundary::ClosedClosed, $before, true];
        yield 'null start contains middle' => [null, $end, IntervalBoundary::ClosedClosed, $middle, true];
        yield 'null start contains after' => [null, $end, IntervalBoundary::ClosedClosed, $after, false];

        yield 'null end contains before' => [$start, null, IntervalBoundary::ClosedClosed, $before, false];
        yield 'null end contains middle' => [$start, null, IntervalBoundary::ClosedClosed, $middle, true];
        yield 'null end contains after' => [$start, null, IntervalBoundary::ClosedClosed, $after, true];

        yield 'both null contains any date' => [null, null, IntervalBoundary::ClosedClosed, $middle, true];
    }

    #[Test]
    public function containsAcceptsDateTimeInterface(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new NullableDateTimeImmutableRange($start, $end);

        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', '2024-06-15') ?: throw new \RuntimeException();
        self::assertTrue($range->contains($dateTime));
    }

    #[Test]
    public function minReturnsCorrectValue(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');

        $range1 = new NullableDateTimeImmutableRange($start, $end);
        self::assertSame($start, $range1->min());

        $range2 = new NullableDateTimeImmutableRange(null, $end);
        self::assertNull($range2->min());
    }

    #[Test]
    public function maxReturnsCorrectValue(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');

        $range1 = new NullableDateTimeImmutableRange($start, $end);
        self::assertSame($end, $range1->max());

        $range2 = new NullableDateTimeImmutableRange($start, null);
        self::assertNull($range2->max());
    }

    #[Test]
    public function publicPropertiesAreAccessible(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new NullableDateTimeImmutableRange($start, $end, IntervalBoundary::OpenClosed);

        self::assertSame($start, $range->start);
        self::assertSame($end, $range->end);
        self::assertSame(IntervalBoundary::OpenClosed, $range->boundary);
    }

    #[Test]
    #[DataProvider('timezoneProvider')]
    public function handlesTimezonesCorrectly(string $startTz, string $endTz): void
    {
        $start = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone($startTz));
        $end = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone($endTz));

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $range = new NullableDateTimeImmutableRange($start, $end);

        self::assertEquals($start, $range->min());
        self::assertEquals($end, $range->max());
    }

    public static function timezoneProvider(): \Iterator
    {
        yield 'UTC to UTC' => ['UTC', 'UTC'];
        yield 'UTC to EST' => ['UTC', 'America/New_York'];
        yield 'EST to PST' => ['America/New_York', 'America/Los_Angeles'];
        yield 'Different timezones' => ['Europe/London', 'Asia/Tokyo'];
    }

    #[Test]
    #[DataProvider('edgeCaseProvider')]
    public function handlesEdgeCasesCorrectly(
        \DateTimeInterface|null $startDate,
        \DateTimeInterface|null $endDate,
    ): void {
        $range = new NullableDateTimeImmutableRange($startDate, $endDate);

        if ($startDate !== null) {
            self::assertEquals($startDate, $range->min());
        } else {
            self::assertNull($range->min());
        }

        if ($endDate !== null) {
            self::assertEquals($endDate, $range->max());
        } else {
            self::assertNull($range->max());
        }
    }

    public static function edgeCaseProvider(): \Iterator
    {
        yield 'Unix epoch' => [
            new \DateTimeImmutable('1970-01-01 00:00:00'),
            new \DateTimeImmutable('2038-01-19 03:14:07'),
        ];
        yield 'null start, far future' => [null, new \DateTimeImmutable('9999-12-31')];
        yield 'far past, null end' => [new \DateTimeImmutable('1000-01-01'), null];
        yield 'Leap year' => [
            new \DateTimeImmutable('2024-02-28'),
            new \DateTimeImmutable('2024-03-01'),
        ];
        yield 'Daylight saving time' => [
            new \DateTimeImmutable('2024-03-10 01:00:00'),
            new \DateTimeImmutable('2024-03-10 03:00:00'),
        ];
        yield 'Year boundary' => [
            new \DateTimeImmutable('2023-12-31 23:59:59'),
            new \DateTimeImmutable('2024-01-01 00:00:00'),
        ];
        yield 'Microsecond precision' => [
            new \DateTimeImmutable('2024-01-01 00:00:00.000001'),
            new \DateTimeImmutable('2024-01-01 00:00:00.999999'),
        ];
        yield 'Both null' => [null, null];
    }

    #[Test]
    #[DataProvider('nullParameterProvider')]
    public function handlesMixedNullAndDateTimeParameters(
        \DateTimeInterface|null $start,
        \DateTimeInterface|null $end,
    ): void {
        $range = new NullableDateTimeImmutableRange($start, $end);

        self::assertSame($start?->format('c'), $range->min()?->format('c'));
        self::assertSame($end?->format('c'), $range->max()?->format('c'));
        self::assertSame($start === null || $end === null, $range->unbounded());
    }

    public static function nullParameterProvider(): \Iterator
    {
        $datetimeImmutable1 = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-06-15 12:00:00') ?: throw new \RuntimeException();
        $datetimeImmutable2 = new \DateTimeImmutable('2024-06-15 12:00:00');

        yield 'DateTimeImmutable from format and null' => [$datetimeImmutable1, null];
        yield 'null and DateTimeImmutable from format' => [null, $datetimeImmutable1];
        yield 'DateTimeImmutable and null' => [$datetimeImmutable2, null];
        yield 'null and DateTimeImmutable' => [null, $datetimeImmutable2];
        yield 'null and null' => [null, null];
    }
}
