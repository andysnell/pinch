<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use PhoneBurner\Pinch\Component\Cache\CacheKey;

interface ThrottlingStateStore
{
    public function get(CacheKey|string $key): RequestThrottlingState;

    public function set(CacheKey|string $key, int $count = 1): RequestThrottlingState;
}
