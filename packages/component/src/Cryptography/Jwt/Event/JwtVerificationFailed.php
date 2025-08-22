<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;

/**
 * Event dispatched when JWT verification fails
 */
#[Psr14Event]
final readonly class JwtVerificationFailed implements Loggable
{
    public function __construct(
        public Jwt $jwt,
        public \Throwable $exception,
        public string $algorithm,
        public string|null $keyId = null,
        public string|null $reason = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            level: LogLevel::Warning,
            message: 'JWT verification failed',
            context: [
                'algorithm' => $this->algorithm,
                'key_id' => $this->keyId,
                'reason' => $this->reason ?? $this->exception->getMessage(),
                'exception_class' => $this->exception::class,
                'jwt_length' => \strlen($this->jwt->value),
            ],
        );
    }
}
