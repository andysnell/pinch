<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Config;

use PhoneBurner\Pinch\Component\Configuration\ConfigStruct;
use PhoneBurner\Pinch\Component\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\Pinch\Component\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\Pinch\Component\Http\RateLimiter\NullRequestRateLimiter;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimiter;

final readonly class RateLimitingConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param class-string<RequestRateLimiter> $rate_limiter_class Rate limiter implementation class
     */
    public function __construct(
        public bool $enabled = true,
        public int $default_per_second_max = 10,
        public int $default_per_minute_max = 60,
        public string $rate_limiter_class = NullRequestRateLimiter::class,
        public string $redis_key_prefix = 'rate_limit:',
    ) {
    }
}
