<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Time\Clock\StaticClock;
use PhoneBurner\Pinch\Time\Domain\TimeUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StaticClockTest extends TestCase
{
    #[Test]
    public function happyPath(): void
    {
        $now = CarbonImmutable::now();

        $clock = new StaticClock($now);

        self::assertSame($now, $clock->now());
        \sleep(1);
        self::assertSame($now, $clock->now());
    }

    #[Test]
    public function timestampHappyPath(): void
    {
        $now = CarbonImmutable::now();
        $timestamp = $now->getTimestamp();

        $clock = new StaticClock($now);

        self::assertSame($timestamp, $clock->timestamp());
        \sleep(1);
        self::assertSame($timestamp, $clock->timestamp());
    }

    #[Test]
    public function microtimeHappyPath(): void
    {
        $now = CarbonImmutable::now();
        $timestamp = (float)$now->format('U.u');

        $clock = new StaticClock($now);

        self::assertSame($timestamp, $clock->microtime());
        \sleep(1);
        self::assertSame($timestamp, $clock->microtime());
    }

    #[Test]
    #[DataProvider('providesSleepHappyPathTestsCases')]
    public function sleepHappyPath(int $delay, TimeUnit $unit): void
    {
        $now = CarbonImmutable::now();

        $before = (int)\hrtime(true);
        $return = new StaticClock($now)->sleep($delay, $unit);
        $duration = (int)\hrtime(true) - $before;

        // StaticClock::sleep() always returns true immediately without actually sleeping
        self::assertTrue($return);
        self::assertGreaterThanOrEqual(0, $duration);
        // Should be very fast since StaticClock doesn't actually sleep
        self::assertLessThan(10_000_000, $duration); // Less than 10ms in nanoseconds
    }

    #[Test]
    #[DataProvider('providesConstructorInputs')]
    public function constructorHandlesDifferentInputTypes(
        \DateTimeInterface|string|null $input,
        string $expected_format,
    ): void {
        $clock = new StaticClock($input);
        $result = $clock->now()->format('Y-m-d H:i:s');

        self::assertMatchesRegularExpression($expected_format, $result);
    }

    public static function providesConstructorInputs(): \Generator
    {
        yield 'string datetime' => ['2024-01-15 14:30:45', '/^2024-01-15 14:30:45$/'];
        yield 'DateTimeImmutable object from DateTime' => [new \DateTimeImmutable('2024-01-15 14:30:45'), '/^2024-01-15 14:30:45$/'];
        yield 'DateTimeImmutable object' => [new \DateTimeImmutable('2024-01-15 14:30:45'), '/^2024-01-15 14:30:45$/'];
        yield 'null defaults to now' => [null, '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'];
    }

    public static function providesSleepHappyPathTestsCases(): \Generator
    {
        yield [60, TimeUnit::Second];
        yield [1, TimeUnit::Second];
        yield [100, TimeUnit::Millisecond];
        yield [25_000, TimeUnit::Microsecond];
        yield [250_000_000, TimeUnit::Nanosecond];
    }
}
