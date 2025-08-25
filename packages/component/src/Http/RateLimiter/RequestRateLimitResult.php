<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use DateTimeInterface;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;

/**
 * Result of a request rate limit check. Not that our request rate limiting
 * only tracks per-second and per-minute rates
 *
 * Contains information about whether the request is allowed,
 * remaining limits, and reset times for HTTP headers.
 */
final readonly class RequestRateLimitResult
{
    public function __construct(
        public bool $allowed,
        public int|null $remaining_per_second,
        public int|null $remaining_per_minute,
        public DateTimeInterface $reset_time,
        public RequestRateLimits $rate_limits,
    ) {
    }

    /**
     * Create result for allowed request
     */
    public static function allowed(
        int|null $remaining_per_second,
        int|null $remaining_per_minute,
        DateTimeInterface $reset_time,
        RequestRateLimits $rate_limits,
    ): self {
        return new self(
            allowed: true,
            remaining_per_second: $remaining_per_second,
            remaining_per_minute: $remaining_per_minute,
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );
    }

    /**
     * Create result for blocked request
     */
    public static function blocked(
        DateTimeInterface $reset_time,
        RequestRateLimits $rate_limits,
    ): self {
        return new self(
            allowed: false,
            remaining_per_second: 0,
            remaining_per_minute: 0,
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );
    }

    public function policy(): string|null
    {
        if()
        \sprintf('q=%d;w=1, q=%d;w=60', $result->rate_limits->second, $result->rate_limits->minute)
    }

    public function remaining(): int
    {
        if($this->allowed === false){
            return 0;
        }
    }

    /**
     * Get retry-after seconds for blocked requests
     */
    public function getRetryAfterSeconds(): int
    {
        if ($this->allowed) {
            return 0;
        }

        $now = new \DateTimeImmutable();
        return \max(1, $this->reset_time->getTimestamp() - $now->getTimestamp());
    }
}
