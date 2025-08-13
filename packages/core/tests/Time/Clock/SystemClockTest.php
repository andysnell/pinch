<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Clock;

use PhoneBurner\Pinch\Time\Clock\SystemClock;
use PhoneBurner\Pinch\Time\Domain\TimeUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_MICROSECOND;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_MILLISECOND;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_SECOND;

final class SystemClockTest extends TestCase
{
    #[Test]
    public function nowHappyPath(): void
    {
        $before = new \DateTimeImmutable();
        $now = new SystemClock()->now();
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual($after, $now);
    }

    #[Test]
    public function timestampHappyPath(): void
    {
        $before = \time();
        $now = new SystemClock()->timestamp();
        $after = \time();

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual($after, $now);
    }

    #[Test]
    public function microtimepHappyPath(): void
    {
        $before = \microtime(true);
        $now = new SystemClock()->microtime();
        $after = \microtime(true);

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual($after, $now);
    }

    #[Test]
    #[DataProvider('providesSleepHappyPathTestsCases')]
    public function sleepHappyPath(int $delay, TimeUnit $unit, int $minimum): void
    {
        $before = (int)\hrtime(true);
        $return = new SystemClock()->sleep($delay, $unit);
        $duration = (int)\hrtime(true) - $before;

        self::assertTrue($return);
        self::assertGreaterThanOrEqual($minimum, $duration);
        self::assertLessThan(1.25 * $minimum, $duration);
    }

    public static function providesSleepHappyPathTestsCases(): \Generator
    {
        yield [1, TimeUnit::Second, NANOSECONDS_IN_SECOND];
        yield [100, TimeUnit::Millisecond, 100 * NANOSECONDS_IN_MILLISECOND];
        yield [25_000, TimeUnit::Microsecond, 25_000 * NANOSECONDS_IN_MICROSECOND];
        yield [250_000_000, TimeUnit::Nanosecond, 250_000_000];
    }

    #[Test]
    #[DataProvider('providesInvalidSleepInputs')]
    public function sleepThrowsExceptionForInvalidInputs(
        int $delay,
        TimeUnit $unit,
        string $expected_exception_message,
    ): void {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage($expected_exception_message);

        $clock = new SystemClock();
        $clock->sleep($delay, $unit);
    }

    public static function providesInvalidSleepInputs(): \Generator
    {
        yield 'negative delay' => [-1, TimeUnit::Second, 'Delay must be greater than or equal to zero.'];
        yield 'unsupported unit - Year' => [1, TimeUnit::Year, 'Unsupported time unit for sleep(): Year'];
        yield 'unsupported unit - Month' => [1, TimeUnit::Month, 'Unsupported time unit for sleep(): Month'];
        yield 'unsupported unit - Week' => [1, TimeUnit::Week, 'Unsupported time unit for sleep(): Week'];
        yield 'unsupported unit - Day' => [1, TimeUnit::Day, 'Unsupported time unit for sleep(): Day'];
        yield 'unsupported unit - Hour' => [1, TimeUnit::Hour, 'Unsupported time unit for sleep(): Hour'];
        yield 'unsupported unit - Minute' => [1, TimeUnit::Minute, 'Unsupported time unit for sleep(): Minute'];
    }
}
