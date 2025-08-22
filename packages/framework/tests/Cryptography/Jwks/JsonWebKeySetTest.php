<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKeySet;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JsonWebKeySet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonWebKeySetTest extends TestCase
{
    #[Test]
    public function createsJwksFromValidJson(): void
    {
        $json = '{"keys":[{"kid":"key1","kty":"RSA","use":"sig","alg":"RS256","n":"modulus","e":"AQAB"},{"kid":"key2","kty":"RSA","use":"enc","alg":"RSA-OAEP","n":"modulus2","e":"AQAB"}]}';

        $jwks = JsonWebKeySet::fromJson($json);

        self::assertCount(2, $jwks->keys);
        self::assertSame(2, $jwks->count());

        $key1 = $jwks->keys[0];
        self::assertSame('key1', $key1->key_id);
        self::assertSame('sig', $key1->use);

        $key2 = $jwks->keys[1];
        self::assertSame('key2', $key2->key_id);
        self::assertSame('enc', $key2->use);
    }

    #[Test]
    public function createsJwksFromArray(): void
    {
        $data = [
            'keys' => [
                [
                    'kid' => 'key1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
                [
                    'kid' => 'key2',
                    'kty' => 'EC',
                    'use' => 'sig',
                    'alg' => 'ES256',
                    'crv' => 'P-256',
                    'x' => 'x-coord',
                    'y' => 'y-coord',
                ],
            ],
        ];

        $jwks = JsonWebKeySet::fromArray($data);

        self::assertCount(2, $jwks->keys);
        self::assertSame($data, $jwks->toArray());
    }

    #[Test]
    public function findsKeyById(): void
    {
        $data = [
            'keys' => [
                [
                    'kid' => 'key1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
                [
                    'kid' => 'key2',
                    'kty' => 'RSA',
                    'use' => 'enc',
                    'alg' => 'RSA-OAEP',
                    'n' => 'modulus2',
                    'e' => 'AQAB',
                ],
            ],
        ];

        $jwks = JsonWebKeySet::fromArray($data);

        $found_key = $jwks->findByKeyId('key2');
        self::assertNotNull($found_key);
        self::assertSame('key2', $found_key->key_id);
        self::assertSame('enc', $found_key->use);

        $not_found = $jwks->findByKeyId('nonexistent');
        self::assertNull($not_found);
    }

    #[Test]
    public function getsSigningKeys(): void
    {
        $data = [
            'keys' => [
                [
                    'kid' => 'sign1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
                [
                    'kid' => 'enc1',
                    'kty' => 'RSA',
                    'use' => 'enc',
                    'alg' => 'RSA-OAEP',
                    'n' => 'modulus2',
                    'e' => 'AQAB',
                ],
                [
                    'kid' => 'sign2',
                    'kty' => 'EC',
                    'use' => 'sig',
                    'alg' => 'ES256',
                    'crv' => 'P-256',
                    'x' => 'x-coord',
                    'y' => 'y-coord',
                ],
            ],
        ];

        $jwks = JsonWebKeySet::fromArray($data);

        $signing_keys = $jwks->getSigningKeys();
        self::assertCount(2, $signing_keys);
        self::assertSame('sign1', $signing_keys[0]->key_id);
        self::assertSame('sign2', $signing_keys[1]->key_id);
    }

    #[Test]
    public function getsEncryptionKeys(): void
    {
        $data = [
            'keys' => [
                [
                    'kid' => 'sign1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
                [
                    'kid' => 'enc1',
                    'kty' => 'RSA',
                    'use' => 'enc',
                    'alg' => 'RSA-OAEP',
                    'n' => 'modulus2',
                    'e' => 'AQAB',
                ],
                [
                    'kid' => 'enc2',
                    'kty' => 'RSA',
                    'use' => 'enc',
                    'alg' => 'RSA1_5',
                    'n' => 'modulus3',
                    'e' => 'AQAB',
                ],
            ],
        ];

        $jwks = JsonWebKeySet::fromArray($data);

        $encryption_keys = $jwks->getEncryptionKeys();
        self::assertCount(2, $encryption_keys);
        self::assertSame('enc1', $encryption_keys[0]->key_id);
        self::assertSame('enc2', $encryption_keys[1]->key_id);
    }

    #[Test]
    public function throwsExceptionForInvalidJson(): void
    {
        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('Invalid JSON in JWKS response:');

        JsonWebKeySet::fromJson('{"invalid": json}');
    }

    #[Test]
    public function throwsExceptionForNonObjectJson(): void
    {
        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('Invalid JSON in JWKS response: Root must be an object');

        JsonWebKeySet::fromJson('["not", "an", "object"]');
    }

    #[Test]
    public function throwsExceptionForMissingKeys(): void
    {
        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('JWKS response must contain a "keys" array.');

        JsonWebKeySet::fromArray(['other' => 'data']);
    }

    #[Test]
    public function throwsExceptionForNonArrayKeys(): void
    {
        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('JWKS "keys" must be an array of objects.');

        JsonWebKeySet::fromArray(['keys' => 'not-an-array']);
    }

    #[Test]
    public function throwsExceptionForEmptyKeys(): void
    {
        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('JWKS key set cannot be empty.');

        JsonWebKeySet::fromArray(['keys' => []]);
    }

    #[Test]
    public function throwsExceptionForInvalidKeyStructure(): void
    {
        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('JWKS "keys" must be an array of objects.');

        JsonWebKeySet::fromArray(['keys' => ['not-an-object', 'also-not-an-object']]);
    }
}
