<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;

/**
 * Event dispatched when JWKS fetching fails
 */
#[Psr14Event]
final readonly class JwksFetchFailed implements Loggable
{
    public function __construct(
        public string $jwksUri,
        public \Throwable $exception,
        public string|null $keyId = null,
        public string|null $reason = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            level: LogLevel::Error,
            message: 'JWKS fetch failed',
            context: [
                'jwks_uri' => $this->jwksUri,
                'key_id' => $this->keyId,
                'reason' => $this->reason ?? $this->exception->getMessage(),
                'exception_class' => $this->exception::class,
            ],
        );
    }
}
