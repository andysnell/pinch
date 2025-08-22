<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;

/**
 * Event dispatched when JWKS fetching begins
 */
#[Psr14Event]
final readonly class JwksFetchStarted implements Loggable
{
    public function __construct(
        public string $jwksUri,
        public string|null $keyId = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'JWKS fetch started',
            context: [
                'jwks_uri' => $this->jwksUri,
                'key_id' => $this->keyId,
            ],
        );
    }
}
