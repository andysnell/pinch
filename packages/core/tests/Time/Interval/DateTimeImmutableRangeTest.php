<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Interval;

use PhoneBurner\Pinch\Math\Interval\NullableRange;
use PhoneBurner\Pinch\Math\Interval\Range;
use PhoneBurner\Pinch\Time\Interval\DateTimeImmutableRange;
use PhoneBurner\Pinch\Time\Interval\DateTimeRange;
use PhoneBurner\Pinch\Time\Interval\NullableDateTimeRange;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\IntervalBoundary;

final class DateTimeImmutableRangeTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRangeFromDateTimeImmutable(): void
    {
        $start = new \DateTimeImmutable('2024-01-01 00:00:00');
        $end = new \DateTimeImmutable('2024-12-31 23:59:59');
        $range = new DateTimeImmutableRange($start, $end);

        self::assertEquals($start, $range->min());
        self::assertEquals($end, $range->max());
        self::assertEquals($start, $range->start);
        self::assertEquals($end, $range->end);
        self::assertSame(IntervalBoundary::ClosedClosed, $range->boundary);
        self::assertFalse($range->unbounded());
    }

    #[Test]
    public function constructorConvertsDateTimeToDateTimeImmutable(): void
    {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-01-01 00:00:00') ?: throw new \RuntimeException();
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-31 23:59:59') ?: throw new \RuntimeException();
        $range = new DateTimeImmutableRange($start, $end);

        self::assertInstanceOf(\DateTimeImmutable::class, $range->min());
        self::assertInstanceOf(\DateTimeImmutable::class, $range->max());
        self::assertEquals($start, $range->min());
        self::assertEquals($end, $range->max());
    }

    #[Test]
    public function constructorAllowsEqualStartAndEnd(): void
    {
        $date = new \DateTimeImmutable('2024-06-15 12:00:00');
        $range = new DateTimeImmutableRange($date, $date);

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

        new DateTimeImmutableRange($start, $end);
    }

    #[Test]
    public function constructorAcceptsCustomBoundary(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new DateTimeImmutableRange($start, $end, IntervalBoundary::OpenOpen);

        self::assertSame(IntervalBoundary::OpenOpen, $range->boundary);
    }

    #[Test]
    #[DataProvider('boundaryProvider')]
    public function constructorAcceptsAllBoundaryTypes(IntervalBoundary $boundary): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new DateTimeImmutableRange($start, $end, $boundary);

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
        $range = new DateTimeImmutableRange(
            new \DateTimeImmutable('1970-01-01'),
            new \DateTimeImmutable('2038-01-19'),
        );

        self::assertFalse($range->unbounded());
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function containsWorksCorrectly(
        string $start_date,
        string $end_date,
        IntervalBoundary $boundary,
        string $check_date,
        bool $expected,
    ): void {
        $range = new DateTimeImmutableRange(
            new \DateTimeImmutable($start_date),
            new \DateTimeImmutable($end_date),
            $boundary,
        );

        self::assertSame($expected, $range->contains(new \DateTimeImmutable($check_date)));
    }

    public static function containsProvider(): \Iterator
    {
        $start = '2024-01-01 00:00:00';
        $end = '2024-12-31 23:59:59';
        $middle = '2024-06-15 12:00:00';
        $before = '2023-12-31 23:59:59';
        $after = '2025-01-01 00:00:00';

        yield 'ClosedClosed includes start' => [$start, $end, IntervalBoundary::ClosedClosed, $start, true];
        yield 'ClosedClosed includes end' => [$start, $end, IntervalBoundary::ClosedClosed, $end, true];
        yield 'ClosedClosed includes middle' => [$start, $end, IntervalBoundary::ClosedClosed, $middle, true];
        yield 'ClosedClosed excludes before' => [$start, $end, IntervalBoundary::ClosedClosed, $before, false];
        yield 'ClosedClosed excludes after' => [$start, $end, IntervalBoundary::ClosedClosed, $after, false];

        yield 'OpenOpen excludes start' => [$start, $end, IntervalBoundary::OpenOpen, $start, false];
        yield 'OpenOpen excludes end' => [$start, $end, IntervalBoundary::OpenOpen, $end, false];
        yield 'OpenOpen includes middle' => [$start, $end, IntervalBoundary::OpenOpen, $middle, true];
        yield 'OpenOpen excludes before' => [$start, $end, IntervalBoundary::OpenOpen, $before, false];
        yield 'OpenOpen excludes after' => [$start, $end, IntervalBoundary::OpenOpen, $after, false];

        yield 'OpenClosed excludes start' => [$start, $end, IntervalBoundary::OpenClosed, $start, false];
        yield 'OpenClosed includes end' => [$start, $end, IntervalBoundary::OpenClosed, $end, true];
        yield 'OpenClosed includes middle' => [$start, $end, IntervalBoundary::OpenClosed, $middle, true];

        yield 'ClosedOpen includes start' => [$start, $end, IntervalBoundary::ClosedOpen, $start, true];
        yield 'ClosedOpen excludes end' => [$start, $end, IntervalBoundary::ClosedOpen, $end, false];
        yield 'ClosedOpen includes middle' => [$start, $end, IntervalBoundary::ClosedOpen, $middle, true];

        yield 'microsecond precision' => [
            '2024-01-01 00:00:00.000000',
            '2024-01-01 00:00:00.999999',
            IntervalBoundary::ClosedClosed,
            '2024-01-01 00:00:00.500000',
            true,
        ];
    }

    #[Test]
    public function containsAcceptsDateTimeInterface(): void
    {
        $range = new DateTimeImmutableRange(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-12-31'),
        );

        $datetime = \DateTimeImmutable::createFromFormat('Y-m-d', '2024-06-15') ?: throw new \RuntimeException();
        self::assertTrue($range->contains($datetime));
    }

    #[Test]
    public function minReturnsStartDate(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new DateTimeImmutableRange($start, $end);

        self::assertSame($start, $range->min());
    }

    #[Test]
    public function maxReturnsEndDate(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new DateTimeImmutableRange($start, $end);

        self::assertSame($end, $range->max());
    }

    #[Test]
    public function periodReturnsDatePeriodWithDefaultInterval(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-01-05');
        $range = new DateTimeImmutableRange($start, $end);

        $period = $range->period();

        self::assertInstanceOf(\DatePeriod::class, $period);
        self::assertEquals($start, $period->getStartDate());
        self::assertEquals($end, $period->getEndDate());

        $dates = \iterator_to_array($period);
        self::assertCount(5, $dates); // Jan 1, 2, 3, 4, 5
    }

    #[Test]
    public function periodReturnsDatePeriodWithCustomInterval(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-01-10');
        $range = new DateTimeImmutableRange($start, $end);

        $interval = new TimeInterval(days: 2);
        $period = $range->period($interval);

        $dates = \iterator_to_array($period);
        self::assertCount(5, $dates); // Jan 1, 3, 5, 7, 9
    }

    #[Test]
    #[DataProvider('periodBoundaryProvider')]
    public function periodRespectsIntervalBoundary(
        IntervalBoundary $boundary,
        bool $expect_start_included,
        bool $expect_end_included,
    ): void {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-01-03');
        $range = new DateTimeImmutableRange($start, $end, $boundary);

        $period = $range->period(new TimeInterval(days: 1));
        $dates = \iterator_to_array($period);

        if ($expect_start_included) {
            self::assertEquals($start, $dates[\array_key_first($dates)]);
        } elseif ($dates !== []) {
            self::assertNotEquals($start, $dates[\array_key_first($dates)]);
        }

        if ($expect_end_included && $dates !== []) {
            self::assertEquals($end, $dates[\array_key_last($dates)]);
        } elseif ($dates !== []) {
            self::assertNotEquals($end, $dates[\array_key_last($dates)]);
        }
    }

    public static function periodBoundaryProvider(): \Iterator
    {
        yield 'ClosedClosed includes both' => [IntervalBoundary::ClosedClosed, true, true];
        yield 'ClosedOpen includes start only' => [IntervalBoundary::ClosedOpen, true, false];
        yield 'OpenClosed includes end only' => [IntervalBoundary::OpenClosed, false, true];
        yield 'OpenOpen excludes both' => [IntervalBoundary::OpenOpen, false, false];
    }

    #[Test]
    public function publicPropertiesAreAccessible(): void
    {
        $start = new \DateTimeImmutable('2024-01-01');
        $end = new \DateTimeImmutable('2024-12-31');
        $range = new DateTimeImmutableRange($start, $end, IntervalBoundary::OpenClosed);

        self::assertSame($start, $range->start);
        self::assertSame($end, $range->end);
        self::assertSame(IntervalBoundary::OpenClosed, $range->boundary);
    }

    #[Test]
    #[DataProvider('timezoneProvider')]
    public function handlesTimezonesCorrectly(string $start_tz, string $end_tz): void
    {
        $start = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone($start_tz));
        $end = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone($end_tz));

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $range = new DateTimeImmutableRange($start, $end);

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
    public function implementsExpectedInterfaces(): void
    {
        $range = new DateTimeImmutableRange(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-12-31'),
        );

        self::assertInstanceOf(DateTimeRange::class, $range);
        self::assertInstanceOf(NullableDateTimeRange::class, $range);
        self::assertInstanceOf(Range::class, $range);
        self::assertInstanceOf(NullableRange::class, $range);
    }

    #[Test]
    #[DataProvider('edgeCaseProvider')]
    public function handlesEdgeCasesCorrectly(string $start_date, string $end_date): void
    {
        $start = new \DateTimeImmutable($start_date);
        $end = new \DateTimeImmutable($end_date);
        $range = new DateTimeImmutableRange($start, $end);

        self::assertEquals($start, $range->min());
        self::assertEquals($end, $range->max());
    }

    public static function edgeCaseProvider(): \Iterator
    {
        yield 'Unix epoch' => ['1970-01-01 00:00:00', '2038-01-19 03:14:07'];
        yield 'Leap year' => ['2024-02-28', '2024-03-01'];
        yield 'Daylight saving time' => ['2024-03-10 01:00:00', '2024-03-10 03:00:00'];
        yield 'Year boundary' => ['2023-12-31 23:59:59', '2024-01-01 00:00:00'];
        yield 'Far future' => ['2024-01-01', '9999-12-31'];
        yield 'Microsecond precision' => ['2024-01-01 00:00:00.000001', '2024-01-01 00:00:00.999999'];
    }
}
