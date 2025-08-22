<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;

/**
 * Event dispatched when JWKS cache is invalidated
 */
#[Psr14Event]
final readonly class JwksCacheInvalidated implements Loggable
{
    public function __construct(
        public string $jwksUri,
        public string $reason,
        public string|null $triggeredBy = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            level: LogLevel::Info,
            message: 'JWKS cache invalidated',
            context: [
                'jwks_uri' => $this->jwksUri,
                'reason' => $this->reason,
                'triggered_by' => $this->triggeredBy,
            ],
        );
    }
}
