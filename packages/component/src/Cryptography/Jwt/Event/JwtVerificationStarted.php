<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;

/**
 * Event dispatched when JWT verification begins
 */
#[Psr14Event]
final readonly class JwtVerificationStarted implements Loggable
{
    public function __construct(
        public Jwt $jwt,
        public string $algorithm,
        public string|null $keyId = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'JWT verification started',
            context: [
                'algorithm' => $this->algorithm,
                'key_id' => $this->keyId,
                'jwt_length' => \strlen($this->jwt->value),
            ],
        );
    }
}
