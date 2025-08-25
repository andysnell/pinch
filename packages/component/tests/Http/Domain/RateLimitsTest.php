<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Domain;

use PhoneBurner\Pinch\Component\Http\RateLimiter\InvalidRateLimits;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RateLimitsTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRateLimits(): void
    {
        $limits = new RequestRateLimits(id: 'test', second: 5, minute: 100);

        self::assertSame('test', $limits->id);
        self::assertSame(5, $limits->second);
        self::assertSame(100, $limits->minute);
    }

    #[Test]
    public function constructorUsesDefaultValues(): void
    {
        $limits = new RequestRateLimits(id: 'test');

        self::assertSame('test', $limits->id);
        self::assertSame(10, $limits->second);
        self::assertSame(60, $limits->minute);
    }

    #[Test]
    public function constructorThrowsExceptionForEmptyId(): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Rate limit ID cannot be empty');

        new RequestRateLimits(id: '');
    }

    #[Test]
    #[DataProvider('invalidPerSecondProvider')]
    public function constructorThrowsExceptionForInvalidPerSecond(int $per_second): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Per-second limit must be positive');

        new RequestRateLimits(id: 'test', second: $per_second);
    }

    #[Test]
    #[DataProvider('invalidPerMinuteProvider')]
    public function constructorThrowsExceptionForInvalidPerMinute(int $per_minute): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Per-minute limit must be positive');

        new RequestRateLimits(id: 'test', minute: $per_minute);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPerMinuteLessThanPerSecond(): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Per-minute limit (5) cannot be less than per-second limit (10)');

        new RequestRateLimits(id: 'test', second: 10, minute: 5);
    }

    public static function invalidPerSecondProvider(): \Iterator
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'very negative' => [-100];
    }

    public static function invalidPerMinuteProvider(): \Iterator
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'very negative' => [-100];
    }
}
