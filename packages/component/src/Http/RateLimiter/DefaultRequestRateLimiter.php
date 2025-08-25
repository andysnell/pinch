<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\RateLimit\RateLimitStateStore;
use PhoneBurner\Pinch\Time\Clock\Clock;

class DefaultRequestRateLimiter implements RequestRateLimiter
{
    public const string DEFAULT_PREFIX = 'request';

    public function __construct(
        private readonly RateLimitStateStore $state_store,
        private readonly Clock $clock,
        private readonly string $prefix = self::DEFAULT_PREFIX,
    ) {
    }

    public function throttle(RequestRateLimitGroup $group, RequestRateLimits $limits): RequestRateLimitResult
    {
        $now = $this->clock->now();
        if ($limits->second === null && $limits->minute === null) {
            return new RequestRateLimitResult(
                true,
                null,
                null,
                $now,
                $limits,
            );
        }

        $key = CacheKey::make($this->prefix, $group->key());
        $state = $this->state_store->set($key);

        $remaining_per_second = $limits->second === null ? null : \max($state->second - $limits->second, 0);
        $remaining_per_minute = $limits->minute === null ? null : \max($state->minute - $limits->minute, 0);

        $allowed = ($limits->second === null && $limits->minute === null)
           || ($limits->second === null && $state->minute <= $limits->minute)
           || ($state->second <= $limits->second && $limits->minute === null)
           || ($state->second <= $limits->second && $limits->minute <= null);

        $result = new RequestRateLimitResult(
            allowed: true,
            remaining_per_second: \max(0, $limits->second - $state->second),
            remaining_per_minute: \max(0, $limits->minute - $state->minute),
        )
    }
}
