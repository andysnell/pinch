<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationFailed;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtVerificationFailedTest extends TestCase
{
    #[Test]
    public function constructorWithRequiredParameters(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $exception = new InvalidJwtToken('Signature verification failed');
        $algorithm = 'RS256';

        $event = new JwtVerificationFailed($jwt, $exception, $algorithm);

        self::assertSame($jwt, $event->jwt);
        self::assertSame($exception, $event->exception);
        self::assertSame($algorithm, $event->algorithm);
        self::assertNull($event->keyId);
        self::assertNull($event->reason);
    }

    #[Test]
    public function constructorWithAllParameters(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $exception = new InvalidJwtToken('Signature verification failed');
        $algorithm = 'RS256';
        $keyId = 'key-123';
        $reason = 'signature_mismatch';

        $event = new JwtVerificationFailed($jwt, $exception, $algorithm, $keyId, $reason);

        self::assertSame($jwt, $event->jwt);
        self::assertSame($exception, $event->exception);
        self::assertSame($algorithm, $event->algorithm);
        self::assertSame($keyId, $event->keyId);
        self::assertSame($reason, $event->reason);
    }

    #[Test]
    public function implementsLoggable(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $exception = new InvalidJwtToken('Signature verification failed');

        $event = new JwtVerificationFailed($jwt, $exception, 'RS256', 'key-123', 'signature_mismatch');

        $logEntry = $event->getLogEntry();

        self::assertSame('JWT verification failed', $logEntry->message);
        self::assertSame(LogLevel::Warning, $logEntry->level);
        self::assertSame('RS256', $logEntry->context['algorithm']);
        self::assertSame('key-123', $logEntry->context['key_id']);
        self::assertSame('signature_mismatch', $logEntry->context['reason']);
        self::assertSame(InvalidJwtToken::class, $logEntry->context['exception_class']);
        self::assertTrue(\is_int($logEntry->context['jwt_length']));
    }

    #[Test]
    public function getLogEntryWithoutReasonUsesExceptionMessage(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $exception = new InvalidJwtToken('Signature verification failed');

        $event = new JwtVerificationFailed($jwt, $exception, 'HS256');

        $logEntry = $event->getLogEntry();

        self::assertSame('JWT verification failed', $logEntry->message);
        self::assertSame(LogLevel::Warning, $logEntry->level);
        self::assertSame('HS256', $logEntry->context['algorithm']);
        self::assertNull($logEntry->context['key_id']);
        self::assertSame('Signature verification failed', $logEntry->context['reason']);
        self::assertSame(InvalidJwtToken::class, $logEntry->context['exception_class']);
    }
}
