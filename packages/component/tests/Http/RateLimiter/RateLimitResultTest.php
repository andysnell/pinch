<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\RateLimiter;

use DateTimeImmutable;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitResult;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RateLimitResultTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidResult(): void
    {
        $reset_time = new DateTimeImmutable('+1 minute');
        $rate_limits = new RequestRateLimits(id: 'test', second: 10, minute: 60);

        $result = new RequestRateLimitResult(
            allowed: true,
            remaining_per_second: 5,
            remaining_per_minute: 30,
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );

        self::assertTrue($result->allowed);
        self::assertSame(5, $result->remaining_per_second);
        self::assertSame(30, $result->remaining_per_minute);
        self::assertSame($reset_time, $result->reset_time);
        self::assertSame($rate_limits, $result->rate_limits);
    }

    #[Test]
    public function allowedFactoryCreatesAllowedResult(): void
    {
        $reset_time = new DateTimeImmutable('+1 minute');
        $rate_limits = new RequestRateLimits(id: 'test', second: 15, minute: 90);

        $result = RequestRateLimitResult::allowed(
            remaining_per_second: 8,
            remaining_per_minute: 45,
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );

        self::assertTrue($result->allowed);
        self::assertSame(8, $result->remaining_per_second);
        self::assertSame(45, $result->remaining_per_minute);
        self::assertSame($rate_limits, $result->rate_limits);
    }

    #[Test]
    public function blockedFactoryCreatesBlockedResult(): void
    {
        $reset_time = new DateTimeImmutable('+1 minute');
        $rate_limits = new RequestRateLimits(id: 'test', second: 10, minute: 60);

        $result = RequestRateLimitResult::blocked(
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );

        self::assertFalse($result->allowed);
        self::assertSame(0, $result->remaining_per_second);
        self::assertSame(0, $result->remaining_per_minute);
        self::assertSame($rate_limits, $result->rate_limits);
    }

    #[Test]
    public function getRetryAfterSecondsReturnsZeroForAllowedRequests(): void
    {
        $rate_limits = new RequestRateLimits(id: 'test', second: 10, minute: 60);

        $result = RequestRateLimitResult::allowed(
            remaining_per_second: 5,
            remaining_per_minute: 30,
            reset_time: new DateTimeImmutable('+1 minute'),
            rate_limits: $rate_limits,
        );

        self::assertSame(0, $result->getRetryAfterSeconds());
    }

    #[Test]
    public function getRetryAfterSecondsReturnsPositiveForBlockedRequests(): void
    {
        $reset_time = new DateTimeImmutable('+30 seconds');
        $rate_limits = new RequestRateLimits(id: 'test', second: 10, minute: 60);

        $result = RequestRateLimitResult::blocked(
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );

        $retry_after = $result->getRetryAfterSeconds();
        self::assertGreaterThan(0, $retry_after);
        self::assertLessThanOrEqual(30, $retry_after);
    }

    #[Test]
    public function getRetryAfterSecondsReturnsMinimumOfOneSecond(): void
    {
        // Use a reset time in the past to test minimum value
        $reset_time = new DateTimeImmutable('-10 seconds');
        $rate_limits = new RequestRateLimits(id: 'test', second: 10, minute: 60);

        $result = RequestRateLimitResult::blocked(
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );

        self::assertSame(1, $result->getRetryAfterSeconds());
    }
}
