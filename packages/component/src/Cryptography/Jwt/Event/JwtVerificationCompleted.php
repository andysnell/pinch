<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;

/**
 * Event dispatched when JWT verification completes successfully
 */
#[Psr14Event]
final readonly class JwtVerificationCompleted implements Loggable
{
    public function __construct(
        public Jwt $jwt,
        public DecodedJwtToken $decodedToken,
        public string $algorithm,
        public string|null $keyId = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'JWT verification completed successfully',
            context: [
                'algorithm' => $this->algorithm,
                'key_id' => $this->keyId,
                'subject' => $this->decodedToken->payload->subject(),
                'expires_at' => $this->decodedToken->payload->expiration()?->format('c'),
            ],
        );
    }
}
