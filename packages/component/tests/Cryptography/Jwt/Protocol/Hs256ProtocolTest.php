<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\Hs256Protocol;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class Hs256ProtocolTest extends TestCase
{
    private ClockInterface $clock;
    private Hs256Protocol $protocol;

    protected function setUp(): void
    {
        $this->clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
            }
        };

        $this->protocol = new Hs256Protocol($this->clock);
    }

    #[Test]
    public function signAndVerifyRoundTripSymmetric(): void
    {
        // Generate shared key for HMAC
        $key = SharedKey::generate();

        $header = new JwtHeader(JwtAlgorithm::HS256);
        $payload = new JwtPayload(['sub' => '1234567890', 'name' => 'John Doe'], $this->clock);

        // Sign the JWT with shared key
        $jwt = $this->protocol->signSymmetric($key, $header, $payload);

        // Verify it can be verified
        $decoded = $this->protocol->verifySymmetric($key, $jwt);

        self::assertSame(JwtAlgorithm::HS256, $decoded->header->algorithm);
        self::assertSame('1234567890', $decoded->payload->subject());
        self::assertSame(['sub' => '1234567890', 'name' => 'John Doe'], $decoded->payload->claims);
    }

    #[Test]
    public function verifyRejectsInvalidSymmetricSignature(): void
    {
        $key1 = SharedKey::generate();
        $key2 = SharedKey::generate();

        $header = new JwtHeader(JwtAlgorithm::HS256);
        $payload = new JwtPayload(['sub' => '1234567890'], $this->clock);

        // Sign with first key
        $jwt = $this->protocol->signSymmetric($key1, $header, $payload);

        // Try to verify with different key - should fail
        $this->expectException(InvalidJwtToken::class);
        $this->expectExceptionMessage('JWT signature verification failed');

        $this->protocol->verifySymmetric($key2, $jwt);
    }

    #[Test]
    public function rejectsAsymmetricSigning(): void
    {
        $this->expectException(JwtLogicException::class);
        $this->expectExceptionMessage('HS256 protocol does not support asymmetric signing');

        // This should throw since HS256 doesn't support asymmetric operations
        $keyPair = SignatureKeyPair::generate();
        $header = new JwtHeader(JwtAlgorithm::HS256);
        $payload = new JwtPayload(['sub' => '1234567890'], $this->clock);

        $this->protocol->sign($keyPair, $header, $payload);
    }

    #[Test]
    public function rejectsAsymmetricVerification(): void
    {
        $this->expectException(JwtLogicException::class);
        $this->expectExceptionMessage('HS256 protocol does not support asymmetric verification');

        // This should throw since HS256 doesn't support asymmetric operations
        $keyPair = SignatureKeyPair::generate();
        $jwt = new Jwt('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.invalid');

        $this->protocol->verify($keyPair->public, $jwt);
    }
}
