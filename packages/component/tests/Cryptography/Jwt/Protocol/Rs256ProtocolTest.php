<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\RsaSignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\Rs256Protocol;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class Rs256ProtocolTest extends TestCase
{
    private ClockInterface $clock;
    private Rs256Protocol $protocol;

    protected function setUp(): void
    {
        $this->clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
            }
        };

        $this->protocol = new Rs256Protocol($this->clock);
    }

    #[Test]
    public function signAndVerifyRoundTrip(): void
    {
        // Generate RSA key pair for RS256 testing
        $keyPair = RsaSignatureKeyPair::generate(2048);

        $header = new JwtHeader(JwtAlgorithm::RS256);
        $payload = new JwtPayload(['sub' => '1234567890', 'name' => 'John Doe'], $this->clock);

        // Sign the JWT
        $jwt = $this->protocol->sign($keyPair, $header, $payload);

        // Verify it can be verified
        $decoded = $this->protocol->verify($keyPair->publicKey(), $jwt);

        self::assertSame(JwtAlgorithm::RS256, $decoded->header->algorithm);
        self::assertSame('1234567890', $decoded->payload->subject());
        self::assertSame(['sub' => '1234567890', 'name' => 'John Doe'], $decoded->payload->claims);
    }

    #[Test]
    public function verifyRejectsInvalidSignature(): void
    {
        $keyPair1 = RsaSignatureKeyPair::generate(2048);
        $keyPair2 = RsaSignatureKeyPair::generate(2048);

        $header = new JwtHeader(JwtAlgorithm::RS256);
        $payload = new JwtPayload(['sub' => '1234567890'], $this->clock);

        // Sign with first key
        $jwt = $this->protocol->sign($keyPair1, $header, $payload);

        // Try to verify with different key - should fail
        $this->expectException(InvalidJwtToken::class);
        $this->expectExceptionMessage('JWT signature verification failed');

        $this->protocol->verify($keyPair2->publicKey(), $jwt);
    }
}
