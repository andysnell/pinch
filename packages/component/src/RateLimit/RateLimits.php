<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\RateLimit\Exception\InvalidRateLimits;

/**
 * Value object representing rate limiting configuration
 *
 * Defines per-second and per-minute limits for HTTP requests with validation.
 * Used with ThrottleRequests middleware to control request rates per identifier.
 */
final readonly class RateLimits
{
    public CacheKey $key;

    /**
     * @param CacheKey|string $key Non-empty string identifier for the rate limit group
     * @param int|null $second Maximum requests allowed per second (positive integer or null for unlimited)
     * @param int|null $minute Maximum requests allowed per minute (positive integer or null for unlimited)
     * @throws \PhoneBurner\Pinch\Component\RateLimit\Exception\InvalidRateLimits When validation fails for any parameter
     */
    public function __construct(
        CacheKey|string $key,
        public int|null $second = null,
        public int|null $minute = null,
        public int|null $hour = null,
        public int|null $day = null,
    ) {
        $this->key = $key instanceof CacheKey ? $key : new CacheKey($key);

        // validate that the rate is null or a non-negative integer
        foreach (['second', 'minute', 'hour', 'day'] as $period) {
            if ($this->$period !== null && $this->$period < 0) {
                throw new InvalidRateLimits(\sprintf(
                    "per-%s limit must be non-negative int or null, got: %s",
                    $period,
                    $this->$period,
                ),);
            }
        }

        // Validate that per-minute is at least per-second, etc. (other relations enforced transitively)
        foreach ([['minute', 'second'], ['hour', 'minute'], ['day', 'hour']] as [$a, $b]) {
            if ($this->$a !== null && $this->$b !== null && $this->$a < $this->$b) {
                throw new InvalidRateLimits(
                    \sprintf("per-%s limit (%s) cannot be less than per-%s limit (%s)", $a, $this->$a, $b, $this->$b),
                );
            }
        }
    }
}
