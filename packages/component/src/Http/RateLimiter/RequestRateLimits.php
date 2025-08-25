<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\RateLimit\Exception\InvalidRateLimits;

/**
 * Value object representing rate limiting configuration
 *
 * Defines per-second and per-minute limits for HTTP requests with validation.
 * Used with ThrottleRequests middleware to control request rates per identifier.
 */
final readonly class RequestRateLimits
{
    public CacheKey $key;

    /**
     * @param CacheKey|string $key Non-empty string identifier for the rate limit group
     * @param int|null $second Maximum requests allowed per second (positive integer or null for unlimited)
     * @param int|null $minute Maximum requests allowed per minute (positive integer or null for unlimited)
     * @throws InvalidRateLimits When validation fails for any parameter
     */
    public function __construct(
        CacheKey|string $key,
        public int|null $second = 10,
        public int|null $minute = 60,
    ) {
        $this->key = $key instanceof CacheKey ? $key : new CacheKey($key);

        if ($this->second !== null && $this->second < 0) {
            throw new InvalidRateLimits(\sprintf(
                "per-second must be non-negative int or null, got: %s",
                $this->second,
            ));
        }

        if ($this->minute !== null && $this->minute < 0) {
            throw new InvalidRateLimits(\sprintf(
                "per-minute limit must be non-negative int or null, got: %s",
                $this->minute,
            ));
        }

        if ($this->minute !== null && $this->second !== null && $this->minute < $this->second) {
            throw new InvalidRateLimits(\sprintf(
                "per-minute limit (%s) cannot be less than per-second limit (%s)",
                $this->minute,
                $this->second,
            ));
        }
    }
}
