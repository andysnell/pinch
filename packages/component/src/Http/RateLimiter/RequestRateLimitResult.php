<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

/**
 * Result of a request rate limit check.
 *
 * Contains information about whether the request is allowed, the applied policies,
 * remaining limits, and reset times for HTTP headers.
 */
interface RequestRateLimitResult
{
    // phpcs:ignore
    public bool $allowed { get; }

    public function policies(): string;

    public function limit(\DateTimeInterface $now): string;

    public function retry(\DateTimeInterface $now): string;
}
