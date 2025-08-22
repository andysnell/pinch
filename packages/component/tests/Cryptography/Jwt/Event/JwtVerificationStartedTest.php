<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationStarted;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtVerificationStartedTest extends TestCase
{
    #[Test]
    public function constructorWithRequiredParameters(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $algorithm = 'RS256';

        $event = new JwtVerificationStarted($jwt, $algorithm);

        self::assertSame($jwt, $event->jwt);
        self::assertSame($algorithm, $event->algorithm);
        self::assertNull($event->keyId);
    }

    #[Test]
    public function constructorWithAllParameters(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $algorithm = 'RS256';
        $keyId = 'key-123';

        $event = new JwtVerificationStarted($jwt, $algorithm, $keyId);

        self::assertSame($jwt, $event->jwt);
        self::assertSame($algorithm, $event->algorithm);
        self::assertSame($keyId, $event->keyId);
    }

    #[Test]
    public function implementsLoggable(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $event = new JwtVerificationStarted($jwt, 'RS256', 'key-123');

        $logEntry = $event->getLogEntry();

        self::assertSame('JWT verification started', $logEntry->message);
        self::assertSame('RS256', $logEntry->context['algorithm']);
        self::assertSame('key-123', $logEntry->context['key_id']);
        self::assertTrue(\is_int($logEntry->context['jwt_length']));
        self::assertGreaterThan(0, $logEntry->context['jwt_length']);
    }

    #[Test]
    public function getLogEntryWithoutKeyId(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $event = new JwtVerificationStarted($jwt, 'HS256');

        $logEntry = $event->getLogEntry();

        self::assertSame('JWT verification started', $logEntry->message);
        self::assertSame('HS256', $logEntry->context['algorithm']);
        self::assertNull($logEntry->context['key_id']);
    }
}
