<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimitResult;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;

/**
 * Event emitted when a request is blocked due to rate limit exceeded
 *
 * This event is fired for the sad path when a request is denied
 * because the rate limits have been exceeded.
 */
#[Psr14Event]
final readonly class RequestRateLimitExceeded implements Loggable
{
    public function __construct(
        public RateLimitResult $result,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(LogLevel::Notice, 'Request Rate Limit Exceeded');
    }
}
