<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitGroup;

/**
 * Interface for rate limiting implementations
 *
 * Implementations should track requests per identifier and enforce
 * per-second and per-minute limits using appropriate storage mechanisms.
 */
interface RequestRateLimiter
{
    /**
     * Check if a request is allowed under the given rate limits
     *
     * @param RequestRateLimits $limits The rate limits to enforce
     * @return RequestRateLimitResult Contains whether the request is allowed and remaining limits
     */
    public function throttle(RequestRateLimitGroup $group, RequestRateLimits $limits): RequestRateLimitResult;
}
