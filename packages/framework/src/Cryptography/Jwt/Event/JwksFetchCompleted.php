<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;

/**
 * Event dispatched when JWKS fetching completes successfully
 */
#[Psr14Event]
final readonly class JwksFetchCompleted implements Loggable
{
    public function __construct(
        public string $jwksUri,
        public int $keyCount,
        public bool $fromCache = false,
        public string|null $keyId = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'JWKS fetch completed successfully',
            context: [
                'jwks_uri' => $this->jwksUri,
                'key_count' => $this->keyCount,
                'from_cache' => $this->fromCache,
                'key_id' => $this->keyId,
            ],
        );
    }
}
