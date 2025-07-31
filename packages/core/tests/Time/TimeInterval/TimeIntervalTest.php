<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\TimeInterval;

use PhoneBurner\Pinch\Time\TimeInterval\Duration;
use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;
use PhoneBurner\Pinch\Time\TimeUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TimeIntervalTest extends TestCase
{
    #[Test]
    public function constructorWithNoParameters(): void
    {
        $interval = new TimeInterval();

        self::assertSame(0, $interval->microseconds);
        self::assertSame(0, $interval->d);
        self::assertSame(0, $interval->h);
        self::assertSame(0, $interval->i);
        self::assertSame(0, $interval->s);
        self::assertSame(0.0, $interval->f);
    }

    #[Test]
    #[DataProvider('constructorParametersProvider')]
    public function constructorWithVariousParameters(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int $expectedMicroseconds,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expectedMicroseconds, $interval->microseconds);
    }

    public static function constructorParametersProvider(): \Generator
    {
        yield 'only days' => [1, 0, 0, 0, 0, 86400000000];
        yield 'only hours' => [0, 1, 0, 0, 0, 3600000000];
        yield 'only minutes' => [0, 0, 1, 0, 0, 60000000];
        yield 'only seconds' => [0, 0, 0, 1, 0, 1000000];
        yield 'only microseconds' => [0, 0, 0, 0, 1000, 1000];
        yield 'all combined' => [1, 2, 3, 4, 5000, 93784005000];
        yield 'float days' => [1.5, 0, 0, 0, 0, 129600000000];
        yield 'float hours' => [0, 1.5, 0, 0, 0, 5400000000];
        yield 'float minutes' => [0, 0, 1.5, 0, 0, 90000000];
        yield 'float seconds' => [0, 0, 0, 1.5, 0, 1500000];
        yield 'negative values clamped to zero' => [-1, -1, -1, -1, -1000, 0];
        yield 'max int overflow clamped' => [\PHP_INT_MAX, 0, 0, 0, 0, \PHP_INT_MAX];
    }

    #[Test]
    #[DataProvider('makeFactoryMethodProvider')]
    public function makeFactoryMethod(mixed $input, int $expectedMicroseconds): void
    {
        $interval = TimeInterval::make($input);

        self::assertSame($expectedMicroseconds, $interval->microseconds);
    }

    public static function makeFactoryMethodProvider(): \Generator
    {
        yield 'from TimeInterval' => [new TimeInterval(seconds: 60), 60000000];
        yield 'from Duration' => [Duration::instance('P1D'), 86400000000];
        yield 'from null returns max' => [null, \PHP_INT_MAX];
        yield 'from int seconds' => [60, 60000000];
        yield 'from float seconds' => [60.5, 60500000];
        yield 'from numeric string' => ['60', 60000000];
    }

    #[Test]
    public function makeFactoryMethodWithInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot Convert Value to Time Interval');

        TimeInterval::make('invalid');
    }

    #[Test]
    public function makeFactoryMethodWithDateTime(): void
    {
        $now = new \DateTimeImmutable('2024-01-01 12:00:00');
        $future = new \DateTimeImmutable('2024-01-01 13:00:00');

        $interval = TimeInterval::make($future, $now);

        self::assertSame(3600000000, $interval->microseconds);
    }

    #[Test]
    #[DataProvider('totalDaysPropertyProvider')]
    public function totalDaysProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int|float $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->total_days);
    }

    public static function totalDaysPropertyProvider(): \Generator
    {
        yield 'zero days' => [0, 0, 0, 0, 0, 0];
        yield 'one day' => [1, 0, 0, 0, 0, 1];
        yield 'two days' => [2, 0, 0, 0, 0, 2];
        yield 'fractional days' => [2.5, 0, 0, 0, 0, 2.5];
        yield '24 hours as days' => [0, 24, 0, 0, 0, 1];
        yield '36 hours as days' => [0, 36, 0, 0, 0, 1.5];
        yield '1440 minutes as days' => [0, 0, 1440, 0, 0, 1];
        yield '86400 seconds as days' => [0, 0, 0, 86400, 0, 1];
        yield 'mixed time units' => [1, 12, 0, 0, 0, 1.5];
        yield 'microseconds as fractional days' => [0, 0, 0, 0, 43200000000, 0.5];
        yield 'max int microseconds' => [0, 0, 0, 0, \PHP_INT_MAX, \PHP_INT_MAX / 86400000000];
    }

    #[Test]
    #[DataProvider('totalHoursPropertyProvider')]
    public function totalHoursProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int|float $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->total_hours);
    }

    public static function totalHoursPropertyProvider(): \Generator
    {
        yield 'zero hours' => [0, 0, 0, 0, 0, 0];
        yield 'one hour' => [0, 1, 0, 0, 0, 1];
        yield 'two hours' => [0, 2, 0, 0, 0, 2];
        yield 'fractional hours' => [0, 2.5, 0, 0, 0, 2.5];
        yield '25 hours' => [0, 25, 0, 0, 0, 25];
        yield '25.5 hours' => [0, 25.5, 0, 0, 0, 25.5];
        yield 'one day as hours' => [1, 0, 0, 0, 0, 24];
        yield 'day and hours' => [1, 12, 0, 0, 0, 36];
        yield '60 minutes as hours' => [0, 0, 60, 0, 0, 1];
        yield '90 minutes as hours' => [0, 0, 90, 0, 0, 1.5];
        yield '3600 seconds as hours' => [0, 0, 0, 3600, 0, 1];
        yield 'microseconds as fractional hours' => [0, 0, 0, 0, 1800000000, 0.5];
        yield 'max int microseconds' => [0, 0, 0, 0, \PHP_INT_MAX, \PHP_INT_MAX / 3600000000];
    }

    #[Test]
    #[DataProvider('totalMinutesPropertyProvider')]
    public function totalMinutesProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int|float $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->total_minutes);
    }

    public static function totalMinutesPropertyProvider(): \Generator
    {
        yield 'zero minutes' => [0, 0, 0, 0, 0, 0];
        yield 'one minute' => [0, 0, 1, 0, 0, 1];
        yield 'two minutes' => [0, 0, 2, 0, 0, 2];
        yield 'fractional minutes' => [0, 0, 2.5, 0, 0, 2.5];
        yield '90 minutes' => [0, 0, 90, 0, 0, 90];
        yield 'one hour as minutes' => [0, 1, 0, 0, 0, 60];
        yield '1.5 hours as minutes' => [0, 1.5, 0, 0, 0, 90];
        yield 'one day as minutes' => [1, 0, 0, 0, 0, 1440];
        yield 'day and minutes' => [1, 0, 30, 0, 0, 1470];
        yield '60 seconds as minutes' => [0, 0, 0, 60, 0, 1];
        yield '90 seconds as minutes' => [0, 0, 0, 90, 0, 1.5];
        yield 'microseconds as fractional minutes' => [0, 0, 0, 0, 30000000, 0.5];
        yield 'max int microseconds' => [0, 0, 0, 0, \PHP_INT_MAX, \PHP_INT_MAX / 60000000];
    }

    #[Test]
    #[DataProvider('totalSecondsPropertyProvider')]
    public function totalSecondsProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int|float $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->total_seconds);
    }

    public static function totalSecondsPropertyProvider(): \Generator
    {
        yield 'zero seconds' => [0, 0, 0, 0, 0, 0];
        yield 'one second' => [0, 0, 0, 1, 0, 1];
        yield 'two seconds' => [0, 0, 0, 2, 0, 2];
        yield 'fractional seconds' => [0, 0, 0, 2.5, 0, 2.5];
        yield '3661 seconds' => [0, 0, 0, 3661, 0, 3661];
        yield 'one minute as seconds' => [0, 0, 1, 0, 0, 60];
        yield 'one hour as seconds' => [0, 1, 0, 0, 0, 3600];
        yield 'one day as seconds' => [1, 0, 0, 0, 0, 86400];
        yield 'mixed units as seconds' => [1, 1, 1, 1, 0, 90061];
        yield 'microseconds as fractional seconds' => [0, 0, 0, 0, 500000, 0.5];
        yield 'microseconds as fractional seconds 2' => [0, 0, 0, 0, 1500000, 1.5];
        yield 'max int microseconds' => [0, 0, 0, 0, \PHP_INT_MAX, \PHP_INT_MAX / 1000000];
    }

    #[Test]
    #[DataProvider('hoursPropertyProvider')]
    public function hoursProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->hours);
    }

    public static function hoursPropertyProvider(): \Generator
    {
        yield 'zero hours' => [0, 0, 0, 0, 0, 0];
        yield 'one hour' => [0, 1, 0, 0, 0, 1];
        yield 'two hours' => [0, 2, 0, 0, 0, 2];
        yield 'fractional hours truncated' => [0, 2.5, 0, 0, 0, 2];
        yield 'fractional hours truncated 2' => [0, 2.9, 0, 0, 0, 2];
        yield '25 hours' => [0, 25, 0, 0, 0, 25];
        yield '25.5 hours truncated' => [0, 25.5, 0, 0, 0, 25];
        yield 'hours from minutes' => [0, 0, 120, 0, 0, 2];
        yield 'hours from mixed' => [0, 1, 60, 0, 0, 2];
        yield 'max hours' => [0, \PHP_INT_MAX / 3600000000, 0, 0, 0, (int)(\PHP_INT_MAX / 3600000000)];
    }

    #[Test]
    #[DataProvider('minutesPropertyProvider')]
    public function minutesProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->minutes);
    }

    public static function minutesPropertyProvider(): \Generator
    {
        yield 'zero minutes' => [0, 0, 0, 0, 0, 0];
        yield 'one minute' => [0, 0, 1, 0, 0, 1];
        yield 'two minutes' => [0, 0, 2, 0, 0, 2];
        yield 'fractional minutes truncated' => [0, 0, 2.5, 0, 0, 2];
        yield 'fractional minutes truncated 2' => [0, 0, 2.9, 0, 0, 2];
        yield '125 minutes' => [0, 0, 125, 0, 0, 125];
        yield 'minutes from seconds' => [0, 0, 0, 120, 0, 2];
        yield 'minutes from mixed' => [0, 0, 1, 60, 0, 2];
        yield 'minutes from hours' => [0, 2, 0, 0, 0, 120];
        yield 'max minutes' => [0, 0, \PHP_INT_MAX / 60000000, 0, 0, (int)(\PHP_INT_MAX / 60000000)];
    }

    #[Test]
    #[DataProvider('secondsPropertyProvider')]
    public function secondsProperty(
        int|float $days,
        int|float $hours,
        int|float $minutes,
        int|float $seconds,
        int $microseconds,
        int $expected,
    ): void {
        $interval = new TimeInterval($days, $hours, $minutes, $seconds, $microseconds);

        self::assertSame($expected, $interval->seconds);
    }

    public static function secondsPropertyProvider(): \Generator
    {
        yield 'zero seconds' => [0, 0, 0, 0, 0, 0];
        yield 'one second' => [0, 0, 0, 1, 0, 1];
        yield 'two seconds' => [0, 0, 0, 2, 0, 2];
        yield 'fractional seconds truncated' => [0, 0, 0, 2.5, 0, 2];
        yield 'fractional seconds truncated 2' => [0, 0, 0, 2.9, 0, 2];
        yield '125 seconds' => [0, 0, 0, 125, 0, 125];
        yield 'seconds from microseconds' => [0, 0, 0, 0, 2000000, 2];
        yield 'seconds from mixed' => [0, 0, 0, 1, 500000, 1];
        yield 'seconds from minutes' => [0, 0, 2, 0, 0, 120];
        yield 'max seconds' => [0, 0, 0, \PHP_INT_MAX / 1000000, 0, (int)(\PHP_INT_MAX / 1000000)];
    }

    #[Test]
    public function dateStringProperty(): void
    {
        $interval = new TimeInterval(days: 1, hours: 2, minutes: 3, seconds: 4);

        self::assertStringContainsString('1 days', $interval->date_string);
        self::assertStringContainsString('2 hours', $interval->date_string);
        self::assertStringContainsString('3 minutes', $interval->date_string);
        self::assertStringContainsString('4 seconds', $interval->date_string);
    }

    #[Test]
    public function dateStringPropertyWithZeroInterval(): void
    {
        $interval = new TimeInterval();

        self::assertSame('0 seconds', $interval->date_string);
    }

    #[Test]
    public function durationProperty(): void
    {
        $interval = new TimeInterval(days: 1);
        $duration = $interval->duration;

        self::assertInstanceOf(Duration::class, $duration);
        self::assertSame(1, $duration->days);
    }

    #[Test]
    public function untilMethod(): void
    {
        $now = new \DateTimeImmutable('2024-01-01 12:00:00');
        $future = new \DateTimeImmutable('2024-01-01 13:30:45');

        $interval = TimeInterval::until($future, $now);

        self::assertSame(5445000000, $interval->microseconds);
        self::assertSame(0, $interval->d);
        self::assertSame(1, $interval->h);
        self::assertSame(30, $interval->i);
        self::assertSame(45, $interval->s);
    }

    #[Test]
    public function instanceMethodWithTimeInterval(): void
    {
        $original = new TimeInterval(hours: 2);
        $instance = TimeInterval::instance($original);

        self::assertSame($original, $instance);
    }

    #[Test]
    public function instanceMethodWithDateInterval(): void
    {
        $dateInterval = new \DateInterval('P1DT2H3M4S');
        $instance = TimeInterval::instance($dateInterval);

        self::assertSame(93784000000, $instance->microseconds);
        self::assertSame(1, $instance->d);
        self::assertSame(2, $instance->h);
        self::assertSame(3, $instance->i);
        self::assertSame(4, $instance->s);
    }

    #[Test]
    public function instanceMethodWithDateIntervalWithFractionalSeconds(): void
    {
        $dateInterval = new \DateInterval('PT1S');
        $dateInterval->f = 0.5;

        $instance = TimeInterval::instance($dateInterval);

        self::assertSame(1500000, $instance->microseconds);
    }

    #[Test]
    public function instanceMethodWithInvalidDateInterval(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot Create a TimeInterval from DateInterval with Non-Zero Year or Month Values');

        $dateInterval = new \DateInterval('P1Y');
        TimeInterval::instance($dateInterval);
    }

    #[Test]
    public function addMethod(): void
    {
        $interval1 = new TimeInterval(hours: 1);
        $interval2 = new TimeInterval(minutes: 30);

        $result = $interval1->add($interval2);

        self::assertNotSame($interval1, $result);
        self::assertSame(5400000000, $result->microseconds);
    }

    #[Test]
    public function subMethod(): void
    {
        $interval1 = new TimeInterval(hours: 2);
        $interval2 = new TimeInterval(minutes: 30);

        $result = $interval1->sub($interval2);

        self::assertNotSame($interval1, $result);
        self::assertSame(5400000000, $result->microseconds);
    }

    #[Test]
    public function subMethodWithLargerInterval(): void
    {
        $interval1 = new TimeInterval(minutes: 30);
        $interval2 = new TimeInterval(hours: 1);

        $result = $interval1->sub($interval2);

        self::assertSame(0, $result->microseconds);
    }

    #[Test]
    public function maxMethod(): void
    {
        $interval = TimeInterval::max();

        self::assertSame(\PHP_INT_MAX, $interval->microseconds);
    }

    #[Test]
    public function minMethod(): void
    {
        $interval = TimeInterval::min();

        self::assertSame(0, $interval->microseconds);
    }

    #[Test]
    public function createMethod(): void
    {
        $interval = new TimeInterval(hours: 1);
        $newInterval = $interval->create(days: 1, hours: 2);

        self::assertNotSame($interval, $newInterval);
        self::assertSame(93600000000, $newInterval->microseconds);
    }

    #[Test]
    public function plusMethod(): void
    {
        $interval = new TimeInterval(hours: 1);
        $result = $interval->plus(minutes: 30, seconds: 15);

        self::assertNotSame($interval, $result);
        self::assertSame(5415000000, $result->microseconds);
    }

    #[Test]
    public function minusMethod(): void
    {
        $interval = new TimeInterval(hours: 2);
        $result = $interval->minus(minutes: 30);

        self::assertNotSame($interval, $result);
        self::assertSame(5400000000, $result->microseconds);
    }

    #[Test]
    public function minusMethodWithLargerValue(): void
    {
        $interval = new TimeInterval(minutes: 30);
        $result = $interval->minus(hours: 1);

        self::assertSame(0, $result->microseconds);
    }

    #[Test]
    public function compareMethod(): void
    {
        $interval1 = new TimeInterval(hours: 1);
        $interval2 = new TimeInterval(hours: 2);
        $interval3 = new TimeInterval(minutes: 60);

        self::assertSame(-1, $interval1->compare($interval2));
        self::assertSame(1, $interval2->compare($interval1));
        self::assertSame(0, $interval1->compare($interval3));
    }

    #[Test]
    public function formatMethod(): void
    {
        $interval = new TimeInterval(days: 1, hours: 2, minutes: 3, seconds: 4);

        self::assertSame('1 day, 2 hours, 3 minutes, 4 seconds', $interval->format('%d day, %h hours, %i minutes, %s seconds'));
        self::assertSame('1:02:03:04', $interval->format('%d:%H:%I:%S'));
    }

    #[Test]
    #[DataProvider('toDecimalUnitProvider')]
    public function toDecimalUnit(TimeInterval $interval, TimeUnit $unit, int $places, string $expected): void
    {
        $result = $interval->toDecimalUnit($unit, $places);

        self::assertSame($expected, $result);
    }

    public static function toDecimalUnitProvider(): \Generator
    {
        $interval = new TimeInterval(days: 1, hours: 12);
        yield 'to days' => [$interval, TimeUnit::Day, 2, '1.50'];
        yield 'to hours' => [$interval, TimeUnit::Hour, 2, '36.00'];
        yield 'to minutes' => [$interval, TimeUnit::Minute, 2, '2160.00'];
        yield 'to seconds' => [$interval, TimeUnit::Second, 2, '129600.00'];
        yield 'to milliseconds' => [$interval, TimeUnit::Millisecond, 2, '129600000.00'];
        yield 'to microseconds' => [$interval, TimeUnit::Microsecond, 2, '129600000000.00'];
        yield 'with more precision' => [$interval, TimeUnit::Day, 4, '1.5000'];
    }

    #[Test]
    public function toDecimalUnitWithInvalidUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Time Unit');

        $interval = new TimeInterval(hours: 1);
        $interval->toDecimalUnit(TimeUnit::Week, 2);
    }

    #[Test]
    public function toArrayMethod(): void
    {
        $interval = new TimeInterval(days: 1, hours: 2, minutes: 3, seconds: 4, microseconds: 5000);

        $expected = [
            'days' => 1,
            'hours' => 2,
            'minutes' => 3,
            'seconds' => 4,
            'microseconds' => 93784005000,
        ];

        self::assertSame($expected, $interval->toArray());
    }

    #[Test]
    public function toArrayMethodWithFilter(): void
    {
        $interval = new TimeInterval(days: 1, seconds: 30);

        $expected = [
            'days' => 1,
            'seconds' => 30,
            'microseconds' => 86430000000,
        ];

        self::assertSame($expected, $interval->toArray(true));
    }

    #[Test]
    public function toStringMethod(): void
    {
        $interval = new TimeInterval(days: 1, hours: 2, minutes: 3, seconds: 4);

        self::assertSame('P1DT2H3M4S', (string)$interval);
    }

    #[Test]
    public function serialization(): void
    {
        $interval = new TimeInterval(days: 1, hours: 2, minutes: 3, seconds: 4);

        $serialized = \serialize($interval);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(TimeInterval::class, $unserialized);
        self::assertSame($interval->microseconds, $unserialized->microseconds);
        self::assertSame($interval->d, $unserialized->d);
        self::assertSame($interval->h, $unserialized->h);
        self::assertSame($interval->i, $unserialized->i);
        self::assertSame($interval->s, $unserialized->s);
    }

    #[Test]
    public function createFromDurationWithValidDuration(): void
    {
        $duration = Duration::instance('P1DT2H3M4S');
        $interval = TimeInterval::make($duration);

        self::assertSame(93784000000, $interval->microseconds);
    }

    #[Test]
    public function createFromDurationWithWeeks(): void
    {
        $duration = Duration::instance('P2W');
        $interval = TimeInterval::make($duration);

        self::assertSame(1209600000000, $interval->microseconds);
    }

    #[Test]
    public function createFromDurationWithYears(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('non-zero years property');

        $duration = Duration::instance('P1Y');
        TimeInterval::make($duration);
    }

    #[Test]
    public function createFromDurationWithMonths(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('non-zero months property');

        $duration = Duration::instance('P1M');
        TimeInterval::make($duration);
    }

    #[Test]
    public function immutability(): void
    {
        $interval = new TimeInterval(hours: 1);
        $original = $interval->microseconds;

        $interval->plus(hours: 1);
        $interval->minus(minutes: 30);
        $interval->add(new TimeInterval(days: 1));
        $interval->sub(new TimeInterval(seconds: 30));

        self::assertSame($original, $interval->microseconds);
    }

    #[Test]
    public function dateIntervalCompatibility(): void
    {
        $interval = new TimeInterval(days: 1, hours: 2, minutes: 3, seconds: 4);

        self::assertSame(0, $interval->y);
        self::assertSame(0, $interval->m);
        self::assertSame(1, $interval->d);
        self::assertSame(2, $interval->h);
        self::assertSame(3, $interval->i);
        self::assertSame(4, $interval->s);
        self::assertSame(0, $interval->invert);
        self::assertSame(1, $interval->days);
    }

    #[Test]
    public function fractionalSecondsHandling(): void
    {
        $interval = new TimeInterval(seconds: 1.5);

        self::assertSame(1500000, $interval->microseconds);
        self::assertSame(1, $interval->s);
        self::assertSame(0.5, $interval->f);
    }

    #[Test]
    public function largeValueHandling(): void
    {
        $interval = new TimeInterval(days: 365 * 100);

        self::assertSame(3153600000000000, $interval->microseconds);
        self::assertSame(36500, $interval->d);
    }

    #[Test]
    public function precisionInCalculations(): void
    {
        $interval1 = new TimeInterval(microseconds: 123456);
        $interval2 = new TimeInterval(microseconds: 654321);

        $sum = $interval1->add($interval2);

        self::assertSame(777777, $sum->microseconds);
    }
}
