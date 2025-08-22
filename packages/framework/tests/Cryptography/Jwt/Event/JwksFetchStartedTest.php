<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchStarted;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwksFetchStartedTest extends TestCase
{
    #[Test]
    public function constructorWithRequiredParameters(): void
    {
        $jwksUri = 'https://example.com/.well-known/jwks.json';

        $event = new JwksFetchStarted($jwksUri);

        self::assertSame($jwksUri, $event->jwksUri);
        self::assertNull($event->keyId);
    }

    #[Test]
    public function constructorWithAllParameters(): void
    {
        $jwksUri = 'https://example.com/.well-known/jwks.json';
        $keyId = 'key-123';

        $event = new JwksFetchStarted($jwksUri, $keyId);

        self::assertSame($jwksUri, $event->jwksUri);
        self::assertSame($keyId, $event->keyId);
    }

    #[Test]
    public function implementsLoggable(): void
    {
        $jwksUri = 'https://cognito-idp.us-east-1.amazonaws.com/us-east-1_ABC123DEF/.well-known/jwks.json';
        $keyId = 'key-123';

        $event = new JwksFetchStarted($jwksUri, $keyId);

        $logEntry = $event->getLogEntry();

        self::assertSame('JWKS fetch started', $logEntry->message);
        self::assertSame($jwksUri, $logEntry->context['jwks_uri']);
        self::assertSame($keyId, $logEntry->context['key_id']);
    }

    #[Test]
    public function getLogEntryWithoutKeyId(): void
    {
        $jwksUri = 'https://example.com/.well-known/jwks.json';

        $event = new JwksFetchStarted($jwksUri);

        $logEntry = $event->getLogEntry();

        self::assertSame('JWKS fetch started', $logEntry->message);
        self::assertSame($jwksUri, $logEntry->context['jwks_uri']);
        self::assertNull($logEntry->context['key_id']);
    }
}
