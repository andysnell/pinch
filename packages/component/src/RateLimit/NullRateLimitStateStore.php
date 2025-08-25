<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PhoneBurner\Pinch\Time\Clock\SystemClock;

final class NullRateLimitStateStore implements RateLimitStateStore
{
    public function __construct(private readonly Clock $clock = new SystemClock())
    {
    }

    public function get(string|CacheKey $key): RateLimitState
    {
        return new RateLimitState(new RateLimitStateTimestamps($this->clock->now()));
    }

    public function set(string|CacheKey $key, int $count = 1): RateLimitState
    {
        return new RateLimitState(
            new RateLimitStateTimestamps($this->clock->now()),
            $count,
            $count,
            $count,
            $count,
        );
    }
}
