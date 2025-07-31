<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\Pinch\Time\DAYS_IN_MONTH_MAX;
use const PhoneBurner\Pinch\Time\DAYS_IN_MONTH_MIN;
use const PhoneBurner\Pinch\Time\DAYS_IN_WEEK;
use const PhoneBurner\Pinch\Time\DAYS_IN_YEAR_MAX;
use const PhoneBurner\Pinch\Time\DAYS_IN_YEAR_MIN;
use const PhoneBurner\Pinch\Time\HOURS_IN_DAY;
use const PhoneBurner\Pinch\Time\HOURS_IN_MONTH_MAX;
use const PhoneBurner\Pinch\Time\HOURS_IN_MONTH_MIN;
use const PhoneBurner\Pinch\Time\HOURS_IN_WEEK;
use const PhoneBurner\Pinch\Time\HOURS_IN_YEAR_MAX;
use const PhoneBurner\Pinch\Time\HOURS_IN_YEAR_MIN;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_MILLISECOND;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_SECOND;
use const PhoneBurner\Pinch\Time\MILLISECONDS_IN_SECOND;
use const PhoneBurner\Pinch\Time\MINUTES_IN_DAY;
use const PhoneBurner\Pinch\Time\MINUTES_IN_HOUR;
use const PhoneBurner\Pinch\Time\MONTHS_IN_YEAR;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_MICROSECOND;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_MILLISECOND;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_SECOND;
use const PhoneBurner\Pinch\Time\SECONDS_IN_DAY;
use const PhoneBurner\Pinch\Time\SECONDS_IN_HOUR;
use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

final class TimeFunctionsTest extends TestCase
{
    #[Test]
    #[DataProvider('providesBasicTimeConversions')]
    public function basicTimeConversionsAreCorrect(int $constant, int $expected): void
    {
        self::assertSame($expected, $constant);
    }

    public static function providesBasicTimeConversions(): \Generator
    {
        // Basic unit conversions
        yield 'nanoseconds in microsecond' => [NANOSECONDS_IN_MICROSECOND, 1000];
        yield 'nanoseconds in millisecond' => [NANOSECONDS_IN_MILLISECOND, 1_000_000];
        yield 'nanoseconds in second' => [NANOSECONDS_IN_SECOND, 1_000_000_000];
        yield 'microseconds in millisecond' => [MICROSECONDS_IN_MILLISECOND, 1000];
        yield 'microseconds in second' => [MICROSECONDS_IN_SECOND, 1_000_000];
        yield 'milliseconds in second' => [MILLISECONDS_IN_SECOND, 1000];

        // Time unit conversions
        yield 'seconds in minute' => [SECONDS_IN_MINUTE, 60];
        yield 'seconds in hour' => [SECONDS_IN_HOUR, 3600];
        yield 'seconds in day' => [SECONDS_IN_DAY, 86_400];
        yield 'minutes in hour' => [MINUTES_IN_HOUR, 60];
        yield 'minutes in day' => [MINUTES_IN_DAY, 1440];
        yield 'hours in day' => [HOURS_IN_DAY, 24];
        yield 'hours in week' => [HOURS_IN_WEEK, 168];

        // Calendar units
        yield 'days in week' => [DAYS_IN_WEEK, 7];
        yield 'days in month min' => [DAYS_IN_MONTH_MIN, 28];
        yield 'days in month max' => [DAYS_IN_MONTH_MAX, 31];
        yield 'days in year min' => [DAYS_IN_YEAR_MIN, 365];
        yield 'days in year max' => [DAYS_IN_YEAR_MAX, 366];
        yield 'months in year' => [MONTHS_IN_YEAR, 12];
    }

    #[Test]
    public function calculatedConstantsAreConsistent(): void
    {
        // Verify that compound constants are calculated correctly
        self::assertSame(SECONDS_IN_HOUR, SECONDS_IN_MINUTE * MINUTES_IN_HOUR);
        self::assertSame(SECONDS_IN_DAY, SECONDS_IN_HOUR * HOURS_IN_DAY);
        self::assertSame(MINUTES_IN_DAY, MINUTES_IN_HOUR * HOURS_IN_DAY);
        self::assertSame(HOURS_IN_WEEK, HOURS_IN_DAY * DAYS_IN_WEEK);
    }

    #[Test]
    public function monthAndYearRangesAreRealistic(): void
    {
        // February (28 days) to January/March/May/July/August/October/December (31 days)
        self::assertSame(28, DAYS_IN_MONTH_MIN);
        self::assertSame(31, DAYS_IN_MONTH_MAX);

        // Regular year (365 days) to leap year (366 days)
        self::assertSame(365, DAYS_IN_YEAR_MIN);
        self::assertSame(366, DAYS_IN_YEAR_MAX);

        // Calculated hours ranges
        self::assertSame(HOURS_IN_MONTH_MIN, DAYS_IN_MONTH_MIN * HOURS_IN_DAY);
        self::assertSame(HOURS_IN_MONTH_MAX, DAYS_IN_MONTH_MAX * HOURS_IN_DAY);
        self::assertSame(HOURS_IN_YEAR_MIN, DAYS_IN_YEAR_MIN * HOURS_IN_DAY);
        self::assertSame(HOURS_IN_YEAR_MAX, DAYS_IN_YEAR_MAX * HOURS_IN_DAY);
    }

    #[Test]
    public function nanosecondConversionsAreAccurate(): void
    {
        // Verify precise nanosecond calculations for common time units
        self::assertSame(1000, NANOSECONDS_IN_MICROSECOND);
        self::assertSame(1_000_000, NANOSECONDS_IN_MILLISECOND);
        self::assertSame(1_000_000_000, NANOSECONDS_IN_SECOND);

        // Verify microsecond calculations
        self::assertSame(NANOSECONDS_IN_MILLISECOND, NANOSECONDS_IN_MICROSECOND * MICROSECONDS_IN_MILLISECOND);
        self::assertSame(NANOSECONDS_IN_SECOND, NANOSECONDS_IN_MICROSECOND * MICROSECONDS_IN_SECOND);
    }
}
