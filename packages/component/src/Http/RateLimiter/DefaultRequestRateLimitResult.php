<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;

/**
 * Result of a request rate limit check. Not that our request rate limiting
 * only tracks per-second and per-minute rates
 *
 * Contains information about whether the request is allowed,
 * remaining limits, and reset times for HTTP headers.
 */
final readonly class DefaultRequestRateLimitResult implements RequestRateLimitResult
{
    public function __construct(
        public bool $allowed,
        public int|null $remaining_per_second,
        public int|null $remaining_per_minute,
        public \DateTimeImmutable $reset_time,
        public RequestRateLimits $limits,
    ) {
    }

    /**
     * Create result for allowed request
     */
    public static function allowed(
        int|null $remaining_per_second,
        int|null $remaining_per_minute,
        \DateTimeImmutable $reset_time,
        RequestRateLimits $rate_limits,
    ): self {
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
        \DateTimeImmutable $reset_time,
        RequestRateLimits $rate_limits,
    ): self {
        return new self(
            allowed: false,
            remaining_per_second: 0,
            remaining_per_minute: 0,
            reset_time: $reset_time,
            limits: $rate_limits,
        );
    }

    public function policies(): string
    {
        $second_policy = $this->limits->second === null ? '' : \sprintf('per-second;q=%d;w=1', $this->limits->second);
        $minute_policy = $this->limits->minute === null ? '' : \sprintf('per-minute;q=%d;w=60', $this->limits->minute);
        return match (true) {
            $second_policy && $minute_policy => \sprintf('%s,%s', $second_policy, $minute_policy),
            $second_policy !== '' => $second_policy,
            $minute_policy !== '' => $minute_policy,
            default => '',
        };
    }

    public function limit(\DateTimeInterface $now): string
    {
        if ($this->limits->minute === null && $this->limits->second === null) {
            return '';
        }

        if ($this->limits->second === null || $this->remaining_per_minute <= $this->remaining_per_second) {
            return \sprintf('"per-minute";r=%s,t=%s', $this->remaining_per_minute, $this->retry($now));
        }

        return \sprintf('"per-second";r=%s,t=%s', $this->remaining_per_second, $this->retry($now));
    }

    public function retry(\DateTimeInterface $now): string
    {
        return (string)\max($now->getTimestamp() - $this->reset_time->getTimestamp(), 0);
    }
}
