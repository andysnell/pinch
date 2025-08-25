<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\RedisBridge;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\RateLimit\RateLimitStateTimestamps;
use PhoneBurner\Pinch\Framework\RedisBridge\RedisRateLimitStateStore;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RedisRateLimitStateStoreTest extends TestCase
{
    private \Redis&MockObject $redis;
    private Clock&MockObject $clock;
    private RedisRateLimitStateStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = $this->createMock(\Redis::class);
        $this->clock = $this->createMock(Clock::class);
        $this->store = new RedisRateLimitStateStore($this->redis, $this->clock);
    }

    #[Test]
    public function getCallsSetWithZeroCount(): void
    {
        $key = 'test-key';
        $datetime = new CarbonImmutable('2025-08-25 01:05:47');

        $this->clock->expects($this->once())
            ->method('now')
            ->willReturn($datetime);

        $this->redis->expects($this->once())
            ->method('multi')
            ->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('incr')
            ->with(self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                1 => $arg === 'rate_limit.test_key.s.1756083947',
                2 => $arg === 'rate_limit.test_key.m.1756083900',
                3 => $arg === 'rate_limit.test_key.h.1756083600',
                4 => $arg === 'rate_limit.test_key.d.1756080000',
                default => false,
            }), 0)->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('expireAt')
            ->with(
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 'rate_limit.test_key.s.1756083947',
                    2 => $arg === 'rate_limit.test_key.m.1756083900',
                    3 => $arg === 'rate_limit.test_key.h.1756083600',
                    4 => $arg === 'rate_limit.test_key.d.1756080000',
                    default => false,
                }),
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 1756083948 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    2 => $arg === 1756083960 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    3 => $arg === 1756087200 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    4 => $arg === 1756166400 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    default => false,
                }),
                'NX',
            )->willReturnSelf();

        $this->redis->expects($this->once())
            ->method('exec')
            ->willReturn([0, 0, 0, 0, true, true, true, true]);

        $result = $this->store->get($key);

        self::assertEquals(new RateLimitStateTimestamps($datetime), $result->timestamps);
        self::assertSame(0, $result->second);
        self::assertSame(0, $result->minute);
        self::assertSame(0, $result->hour);
        self::assertSame(0, $result->day);
    }

    #[Test]
    public function getAcceptsCacheKeyObject(): void
    {
        $cacheKey = CacheKey::make('test-key');
        $datetime = new CarbonImmutable('2025-08-25 01:05:47');

        $this->clock->expects($this->once())
            ->method('now')
            ->willReturn($datetime);

        $this->redis->expects($this->once())
            ->method('multi')
            ->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('incr')
            ->with(self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                1 => $arg === 'rate_limit.test_key.s.1756083947',
                2 => $arg === 'rate_limit.test_key.m.1756083900',
                3 => $arg === 'rate_limit.test_key.h.1756083600',
                4 => $arg === 'rate_limit.test_key.d.1756080000',
                default => false,
            }), 0)->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('expireAt')
            ->with(
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 'rate_limit.test_key.s.1756083947',
                    2 => $arg === 'rate_limit.test_key.m.1756083900',
                    3 => $arg === 'rate_limit.test_key.h.1756083600',
                    4 => $arg === 'rate_limit.test_key.d.1756080000',
                    default => false,
                }),
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 1756083948 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    2 => $arg === 1756083960 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    3 => $arg === 1756087200 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    4 => $arg === 1756166400 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    default => false,
                }),
                'NX',
            )->willReturnSelf();

        $this->redis->expects($this->once())
            ->method('exec')
            ->willReturn([5, 10, 15, 20, false, false, false, false]);

        $result = $this->store->get($cacheKey);

        self::assertEquals(new RateLimitStateTimestamps($datetime), $result->timestamps);
        self::assertSame(5, $result->second);
        self::assertSame(10, $result->minute);
        self::assertSame(15, $result->hour);
        self::assertSame(20, $result->day);
    }

    #[Test]
    public function setIncrementsCountersBySpecifiedAmount(): void
    {
        $key = 'test-key';
        $count = 3;
        $datetime = new CarbonImmutable('2025-08-25 01:05:47');

        $this->clock->expects($this->once())
            ->method('now')
            ->willReturn($datetime);

        $this->redis->expects($this->once())
            ->method('multi')
            ->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('incr')
            ->with(self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                1 => $arg === 'rate_limit.test_key.s.1756083947',
                2 => $arg === 'rate_limit.test_key.m.1756083900',
                3 => $arg === 'rate_limit.test_key.h.1756083600',
                4 => $arg === 'rate_limit.test_key.d.1756080000',
                default => false,
            }), 3)->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('expireAt')
            ->with(
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 'rate_limit.test_key.s.1756083947',
                    2 => $arg === 'rate_limit.test_key.m.1756083900',
                    3 => $arg === 'rate_limit.test_key.h.1756083600',
                    4 => $arg === 'rate_limit.test_key.d.1756080000',
                    default => false,
                }),
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 1756083948 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    2 => $arg === 1756083960 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    3 => $arg === 1756087200 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    4 => $arg === 1756166400 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    default => false,
                }),
                'NX',
            )->willReturnSelf();

        $this->redis->expects($this->once())
            ->method('exec')
            ->willReturn([3, 6, 9, 12, false, false, false, false]);

        $result = $this->store->set($key, $count);

        self::assertEquals(new RateLimitStateTimestamps($datetime), $result->timestamps);
        self::assertSame(3, $result->second);
        self::assertSame(6, $result->minute);
        self::assertSame(9, $result->hour);
        self::assertSame(12, $result->day);
    }

    #[Test]
    public function setUsesDefaultCountOfOne(): void
    {
        $key = 'test-key';
        $datetime = new CarbonImmutable('2025-08-25 01:05:47');

        $this->clock->expects($this->once())
            ->method('now')
            ->willReturn($datetime);

        $this->redis->expects($this->once())
            ->method('multi')
            ->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('incr')
            ->with(self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                1 => $arg === 'rate_limit.test_key.s.1756083947',
                2 => $arg === 'rate_limit.test_key.m.1756083900',
                3 => $arg === 'rate_limit.test_key.h.1756083600',
                4 => $arg === 'rate_limit.test_key.d.1756080000',
                default => false,
            }), 1)->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('expireAt')
            ->with(
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 'rate_limit.test_key.s.1756083947',
                    2 => $arg === 'rate_limit.test_key.m.1756083900',
                    3 => $arg === 'rate_limit.test_key.h.1756083600',
                    4 => $arg === 'rate_limit.test_key.d.1756080000',
                    default => false,
                }),
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 1756083948 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    2 => $arg === 1756083960 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    3 => $arg === 1756087200 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    4 => $arg === 1756166400 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    default => false,
                }),
                'NX',
            )->willReturnSelf();

        $this->redis->expects($this->once())
            ->method('exec')
            ->willReturn([1, 1, 1, 1, false, false, false, false]);

        $result = $this->store->set($key);

        self::assertSame(1, $result->second);
        self::assertSame(1, $result->minute);
        self::assertSame(1, $result->hour);
        self::assertSame(1, $result->day);
    }

    #[Test]
    public function setHandlesCacheKeyObjects(): void
    {
        $cache_key = CacheKey::make('user', 123, 'action');
        $datetime = new CarbonImmutable('2025-08-25 01:05:47');

        $this->clock->expects($this->once())
            ->method('now')
            ->willReturn($datetime);

        $this->redis->expects($this->once())
            ->method('multi')
            ->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('incr')
            ->with(self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                1 => $arg === 'rate_limit.user.123.action.s.1756083947',
                2 => $arg === 'rate_limit.user.123.action.m.1756083900',
                3 => $arg === 'rate_limit.user.123.action.h.1756083600',
                4 => $arg === 'rate_limit.user.123.action.d.1756080000',
                default => false,
            }), 1)->willReturnSelf();

        $matcher = $this->exactly(4);
        $this->redis->expects($matcher)
            ->method('expireAt')
            ->with(
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 'rate_limit.user.123.action.s.1756083947',
                    2 => $arg === 'rate_limit.user.123.action.m.1756083900',
                    3 => $arg === 'rate_limit.user.123.action.h.1756083600',
                    4 => $arg === 'rate_limit.user.123.action.d.1756080000',
                    default => false,
                }),
                self::callback(static fn(mixed $arg): bool => match ($matcher->numberOfInvocations()) {
                    1 => $arg === 1756083948 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    2 => $arg === 1756083960 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    3 => $arg === 1756087200 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    4 => $arg === 1756166400 + RedisRateLimitStateStore::CLOCK_DRIFT_ALLOWANCE,
                    default => false,
                }),
                'NX',
            )->willReturnSelf();

        $this->redis->expects($this->once())
            ->method('exec')
            ->willReturn([1, 1, 1, 1, true, true, true, true]);

        $this->store->set($cache_key);
    }
}
