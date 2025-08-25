<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PhoneBurner\Pinch\Time\Clock\SystemClock;

final class NullRequestThrottlingStateStore implements RequestThrottlingStateStore
{
    public function __construct(private readonly Clock $clock = new SystemClock())
    {
    }

    public function get(string|CacheKey $key): RequestThrottlingState
    {
        return new RequestThrottlingState($this->clock->now());
    }

    public function set(string|CacheKey $key, int $count = 1): RequestThrottlingState
    {
        return new RequestThrottlingState($this->clock->now(), $count, $count, $count, $count);
    }
}
