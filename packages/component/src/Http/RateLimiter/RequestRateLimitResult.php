<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use DateTimeInterface;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PhoneBurner\Pinch\Component\RateLimit\RateLimitState;

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
        public RequestRateLimits $limits,
    )
    {
    }

    /**
     * Create result for allowed request
     */
    public static function allowed(
        int|null $remaining_per_second,
        int|null $remaining_per_minute,
        DateTimeInterface $reset_time,
        RequestRateLimits $rate_limits,
    ): self
    {
        return new self(
            allowed: true,
            remaining_per_second: $remaining_per_second,
            remaining_per_minute: $remaining_per_minute,
            reset_time: $reset_time,
            limits: $rate_limits,
        );
    }

    /**
     * Create result for blocked request
     */
    public static function blocked(
        DateTimeInterface $reset_time,
        RequestRateLimits $rate_limits,
    ): self
    {
        return new self(
            allowed: false,
            remaining_per_second: 0,
            remaining_per_minute: 0,
            reset_time: $reset_time,
            limits: $rate_limits,
        );
    }

    public function policy(): string|null
    {
        $second_policy = $this->limits->second === null ? '' : \sprintf('per-second;q=%d;w=1', $this->limits->second);
        $minute_policy = $this->limits->minute === null ? '' : \sprintf('per-minute;q=%d;w=60', $this->limits->minute);
        return match (true) {
            $second_policy && $minute_policy => \sprintf('%s,%s', $second_policy, $minute_policy),
            $second_policy !== '' => $second_policy,
            $minute_policy !== '' => $minute_policy,
            default => null,
        };
    }

    public function limit(): string|null
    {
        if ($this->limits->minute === null && $this->limits->second === null) {
            return null;
        }

        if ($this->limits->second === null || $this->remaining_per_minute <= $this->remaining_per_second){
            return \sprintf('"per-minute";r=%s,t=%s', $this->remaining_per_minute, $this->reset_time);
        }

        return \sprintf('"per-second";r=%s,t=%s', $this->remaining_per_second, $this->reset_time);
    }

    public function remaining(): int
    {
        if ($this->allowed === false) {
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
