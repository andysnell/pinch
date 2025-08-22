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
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\EdDsaProtocol;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class EdDsaProtocolTest extends TestCase
{
    private ClockInterface $clock;
    private EdDsaProtocol $protocol;

    protected function setUp(): void
    {
        $this->clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
            }
        };

        $this->protocol = new EdDsaProtocol($this->clock);
    }

    #[Test]
    public function signAndVerifyRoundTripWithEd25519(): void
    {
        // Generate Ed25519 key pair
        $keyPair = SignatureKeyPair::generate();

        $header = new JwtHeader(JwtAlgorithm::EdDSA);
        $payload = new JwtPayload(['sub' => '1234567890', 'name' => 'John Doe'], $this->clock);

        // Sign the JWT
        $jwt = $this->protocol->sign($keyPair, $header, $payload);

        // Verify it can be verified
        $decoded = $this->protocol->verify($keyPair->public, $jwt);

        self::assertSame(JwtAlgorithm::EdDSA, $decoded->header->algorithm);
        self::assertSame('1234567890', $decoded->payload->subject());
        self::assertSame(['sub' => '1234567890', 'name' => 'John Doe'], $decoded->payload->claims);
    }

    #[Test]
    public function verifyRejectsInvalidSignature(): void
    {
        $keyPair1 = SignatureKeyPair::generate();
        $keyPair2 = SignatureKeyPair::generate();

        $header = new JwtHeader(JwtAlgorithm::EdDSA);
        $payload = new JwtPayload(['sub' => '1234567890'], $this->clock);

        // Sign with first key
        $jwt = $this->protocol->sign($keyPair1, $header, $payload);

        // Try to verify with different key - should fail
        $this->expectException(InvalidJwtToken::class);
        $this->expectExceptionMessage('JWT signature verification failed');

        $this->protocol->verify($keyPair2->public, $jwt);
    }

    #[Test]
    public function rejectsSymmetricSigning(): void
    {
        $this->expectException(JwtLogicException::class);
        $this->expectExceptionMessage('EdDSA protocol does not support symmetric signing');

        // This should throw since EdDSA doesn't support symmetric operations
        $key = SharedKey::generate();
        $header = new JwtHeader(JwtAlgorithm::EdDSA);
        $payload = new JwtPayload(['sub' => '1234567890'], $this->clock);

        $this->protocol->signSymmetric($key, $header, $payload);
    }

    #[Test]
    public function rejectsSymmetricVerification(): void
    {
        $this->expectException(JwtLogicException::class);
        $this->expectExceptionMessage('EdDSA protocol does not support symmetric verification');

        // This should throw since EdDSA doesn't support symmetric operations
        $key = SharedKey::generate();
        $jwt = new Jwt('eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.invalid');

        $this->protocol->verifySymmetric($key, $jwt);
    }
}
