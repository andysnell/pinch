<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitState;

interface RequestRateLimitStateStore
{
    public function get(CacheKey|string $key): RequestRateLimitState;

    public function set(CacheKey|string $key, int $count = 1): RequestRateLimitState;
}
