<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use PhoneBurner\Pinch\Component\Cache\CacheKey;

interface RateLimitStateStore
{
    public function get(CacheKey|string $key): RateLimitState;

    public function set(CacheKey|string $key, int $count = 1): RateLimitState;
}
