<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKey;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JsonWebKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonWebKeyTest extends TestCase
{
    #[Test]
    public function createsRsaSigningKey(): void
    {
        $key_data = [
            'kid' => 'test-key-id',
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ];

        $key = JsonWebKey::fromArray($key_data);

        self::assertSame('test-key-id', $key->key_id);
        self::assertSame('RSA', $key->key_type);
        self::assertSame('sig', $key->use);
        self::assertSame('RS256', $key->algorithm);
        self::assertSame($key_data, $key->key_data);
        self::assertTrue($key->isSigningKey());
        self::assertFalse($key->isEncryptionKey());
        self::assertSame($key_data, $key->toArray());
    }

    #[Test]
    public function createsRsaEncryptionKey(): void
    {
        $key_data = [
            'kid' => 'enc-key-id',
            'kty' => 'RSA',
            'use' => 'enc',
            'alg' => 'RSA-OAEP',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ];

        $key = JsonWebKey::fromArray($key_data);

        self::assertSame('enc-key-id', $key->key_id);
        self::assertSame('RSA', $key->key_type);
        self::assertSame('enc', $key->use);
        self::assertSame('RSA-OAEP', $key->algorithm);
        self::assertFalse($key->isSigningKey());
        self::assertTrue($key->isEncryptionKey());
    }

    #[Test]
    public function createsEcSigningKey(): void
    {
        $key_data = [
            'kid' => 'ec-key-id',
            'kty' => 'EC',
            'use' => 'sig',
            'alg' => 'ES256',
            'crv' => 'P-256',
            'x' => 'test-x-coordinate',
            'y' => 'test-y-coordinate',
        ];

        $key = JsonWebKey::fromArray($key_data);

        self::assertSame('ec-key-id', $key->key_id);
        self::assertSame('EC', $key->key_type);
        self::assertSame('sig', $key->use);
        self::assertSame('ES256', $key->algorithm);
        self::assertTrue($key->isSigningKey());
        self::assertFalse($key->isEncryptionKey());
    }

    #[Test]
    public function throwsExceptionForMissingKid(): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage('JSON Web Key must have a "kid" (key ID) property.');

        JsonWebKey::fromArray([
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ]);
    }

    #[Test]
    public function throwsExceptionForMissingKty(): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage('JSON Web Key must have a "kty" (key type) property.');

        JsonWebKey::fromArray([
            'kid' => 'test-key-id',
            'use' => 'sig',
            'alg' => 'RS256',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ]);
    }

    #[Test]
    public function throwsExceptionForMissingUse(): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage('JSON Web Key must have a "use" property.');

        JsonWebKey::fromArray([
            'kid' => 'test-key-id',
            'kty' => 'RSA',
            'alg' => 'RS256',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ]);
    }

    #[Test]
    public function throwsExceptionForMissingAlg(): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage('JSON Web Key must have an "alg" (algorithm) property.');

        JsonWebKey::fromArray([
            'kid' => 'test-key-id',
            'kty' => 'RSA',
            'use' => 'sig',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ]);
    }

    #[Test]
    #[DataProvider('provideInvalidUseValues')]
    public function throwsExceptionForInvalidUse(string $invalid_use): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage(\sprintf("Invalid key use '%s'. Must be 'sig' or 'enc'.", $invalid_use));

        JsonWebKey::fromArray([
            'kid' => 'test-key-id',
            'kty' => 'RSA',
            'use' => $invalid_use,
            'alg' => 'RS256',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ]);
    }

    #[Test]
    public function throwsExceptionForRsaKeyMissingModulus(): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage("JSON Web Key is missing required property: 'n and e required for RSA keys'.");

        JsonWebKey::fromArray([
            'kid' => 'test-key-id',
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            'e' => 'AQAB',
        ]);
    }

    #[Test]
    public function throwsExceptionForEcKeyMissingCurve(): void
    {
        $this->expectException(InvalidJsonWebKey::class);
        $this->expectExceptionMessage("JSON Web Key is missing required property: 'crv, x, and y required for EC keys'.");

        JsonWebKey::fromArray([
            'kid' => 'test-key-id',
            'kty' => 'EC',
            'use' => 'sig',
            'alg' => 'ES256',
            'x' => 'test-x-coordinate',
            'y' => 'test-y-coordinate',
        ]);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideInvalidUseValues(): \Iterator
    {
        yield 'invalid use' => ['invalid'];
        yield 'uppercase sig' => ['SIG'];
        yield 'uppercase enc' => ['ENC'];
        yield 'sign' => ['sign'];
        yield 'encrypt' => ['encrypt'];
    }
}
