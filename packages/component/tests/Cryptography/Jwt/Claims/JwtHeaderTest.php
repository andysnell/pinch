<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Claims;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtHeaderTest extends TestCase
{
    #[Test]
    public function constructorWithAlgorithm(): void
    {
        $header = new JwtHeader(JwtAlgorithm::RS256);

        self::assertSame(JwtAlgorithm::RS256, $header->algorithm);
        self::assertSame('JWT', $header->type);
        self::assertNull($header->key_id);
    }

    #[Test]
    public function constructorWithAllParameters(): void
    {
        $header = new JwtHeader(
            JwtAlgorithm::HS256,
            'custom-type',
            'key-123',
        );

        self::assertSame(JwtAlgorithm::HS256, $header->algorithm);
        self::assertSame('custom-type', $header->type);
        self::assertSame('key-123', $header->key_id);
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $header = new JwtHeader(JwtAlgorithm::RS256, key_id: 'test-key');
        $json = $header->jsonSerialize();

        self::assertSame([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => 'test-key',
        ], $json);
    }

    #[Test]
    public function jsonSerializeWithoutOptionalFields(): void
    {
        $header = new JwtHeader(JwtAlgorithm::EdDSA);
        $json = $header->jsonSerialize();

        self::assertSame([
            'alg' => 'EdDSA',
            'typ' => 'JWT',
        ], $json);
    }
}
