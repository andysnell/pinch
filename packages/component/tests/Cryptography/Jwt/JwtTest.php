<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtTest extends TestCase
{
    #[Test]
    #[DataProvider('providesValidJwtTokens')]
    public function constructorParsesValidJwtToken(
        string $token_value,
        string $expected_header,
        string $expected_payload,
        string $expected_signature,
    ): void {
        $jwt = new Jwt($token_value);

        self::assertSame($token_value, $jwt->value);
        self::assertSame($expected_header, $jwt->header());
        self::assertSame($expected_payload, $jwt->payload());
        self::assertSame($expected_signature, $jwt->signature());
    }

    #[Test]
    #[DataProvider('providesInvalidJwtTokens')]
    public function constructorThrowsExceptionForInvalidToken(string $invalid_token): void
    {
        $this->expectException(InvalidJwtToken::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        new Jwt($invalid_token);
    }

    #[Test]
    public function implementsStringable(): void
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';
        $jwt = new Jwt($token);

        self::assertSame($token, (string)$jwt);
        self::assertSame($token, $jwt->__toString());
    }

    public static function providesValidJwtTokens(): \Generator
    {
        // Standard JWT with HS256
        yield 'HS256 JWT' => [
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
            '{"alg":"HS256","typ":"JWT"}',
            '{"sub":"1234567890","name":"John Doe","iat":1516239022}',
            'SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
        ];

        // JWT with RS256
        yield 'RS256 JWT' => [
            'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.signature',
            '{"alg":"RS256","typ":"JWT"}',
            '{"sub":"1234567890","name":"John Doe","admin":true}',
            'signature',
        ];
    }

    public static function providesInvalidJwtTokens(): \Generator
    {
        yield 'empty string' => [''];
        yield 'single part' => ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'];
        yield 'two parts only' => ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0'];
        yield 'four parts' => ['a.b.c.d'];
        yield 'invalid base64url characters' => ['header+invalid.payload.signature'];
        yield 'none algorithm token' => ['eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJzdWIiOiIxMjM0NTY3ODkwIn0.'];
    }
}
