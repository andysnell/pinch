<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Event;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationCompleted;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class JwtVerificationCompletedTest extends TestCase
{
    private ClockInterface $clock;

    protected function setUp(): void
    {
        $this->clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
            }
        };
    }

    #[Test]
    public function constructorWithRequiredParameters(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $header = new JwtHeader(JwtAlgorithm::RS256);
        $payload = new JwtPayload(['sub' => '123'], $this->clock);
        $decodedToken = new DecodedJwtToken($header, $payload);
        $algorithm = 'RS256';

        $event = new JwtVerificationCompleted($jwt, $decodedToken, $algorithm);

        self::assertSame($jwt, $event->jwt);
        self::assertSame($decodedToken, $event->decodedToken);
        self::assertSame($algorithm, $event->algorithm);
        self::assertNull($event->keyId);
    }

    #[Test]
    public function constructorWithAllParameters(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $header = new JwtHeader(JwtAlgorithm::RS256, key_id: 'key-123');
        $payload = new JwtPayload(['sub' => '123', 'exp' => 1704110400], $this->clock);
        $decodedToken = new DecodedJwtToken($header, $payload);
        $algorithm = 'RS256';
        $keyId = 'key-123';

        $event = new JwtVerificationCompleted($jwt, $decodedToken, $algorithm, $keyId);

        self::assertSame($jwt, $event->jwt);
        self::assertSame($decodedToken, $event->decodedToken);
        self::assertSame($algorithm, $event->algorithm);
        self::assertSame($keyId, $event->keyId);
    }

    #[Test]
    public function implementsLoggable(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $header = new JwtHeader(JwtAlgorithm::RS256);
        $payload = new JwtPayload(['sub' => '123', 'exp' => 1704110400], $this->clock);
        $decodedToken = new DecodedJwtToken($header, $payload);

        $event = new JwtVerificationCompleted($jwt, $decodedToken, 'RS256', 'key-123');

        $logEntry = $event->getLogEntry();

        self::assertSame('JWT verification completed successfully', $logEntry->message);
        self::assertSame('RS256', $logEntry->context['algorithm']);
        self::assertSame('key-123', $logEntry->context['key_id']);
        self::assertSame('123', $logEntry->context['subject']);
        self::assertNotNull($logEntry->context['expires_at']);
    }

    #[Test]
    public function getLogEntryWithoutExpirationAndKeyId(): void
    {
        $jwt = new Jwt('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMifQ.signature');
        $header = new JwtHeader(JwtAlgorithm::HS256);
        $payload = new JwtPayload(['sub' => '123'], $this->clock);
        $decodedToken = new DecodedJwtToken($header, $payload);

        $event = new JwtVerificationCompleted($jwt, $decodedToken, 'HS256');

        $logEntry = $event->getLogEntry();

        self::assertSame('JWT verification completed successfully', $logEntry->message);
        self::assertSame('HS256', $logEntry->context['algorithm']);
        self::assertNull($logEntry->context['key_id']);
        self::assertSame('123', $logEntry->context['subject']);
        self::assertNull($logEntry->context['expires_at']);
    }
}
