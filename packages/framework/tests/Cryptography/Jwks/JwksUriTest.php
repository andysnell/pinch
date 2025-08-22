<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJwksUri;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwksUriTest extends TestCase
{
    #[Test]
    public function createsJwksUriFromValidHttpsUrl(): void
    {
        $uri = 'https://example.com/.well-known/jwks.json';
        $jwks_uri = JwksUri::fromString($uri);

        self::assertSame($uri, $jwks_uri->value);
        self::assertSame($uri, $jwks_uri->toString());
        self::assertSame($uri, (string)$jwks_uri);
    }

    #[Test]
    public function createsJwksUriWithPort(): void
    {
        $uri = 'https://example.com:8443/.well-known/jwks.json';
        $jwks_uri = JwksUri::fromString($uri);

        self::assertSame($uri, $jwks_uri->value);
    }

    #[Test]
    public function createsJwksUriWithPathAndQuery(): void
    {
        $uri = 'https://cognito-idp.us-east-1.amazonaws.com/us-east-1_ABC123DEF/.well-known/jwks.json?v=1';
        $jwks_uri = JwksUri::fromString($uri);

        self::assertSame($uri, $jwks_uri->value);
    }

    #[Test]
    public function throwsExceptionForNonHttpsUri(): void
    {
        $this->expectException(InvalidJwksUri::class);
        $this->expectExceptionMessage("JWKS URI must use HTTPS for security: 'http://example.com/jwks.json'.");

        JwksUri::fromString('http://example.com/jwks.json');
    }

    #[Test]
    #[DataProvider('provideInvalidUris')]
    public function throwsExceptionForInvalidUris(string $invalid_uri): void
    {
        $this->expectException(InvalidJwksUri::class);
        $this->expectExceptionMessage(\sprintf("Invalid JWKS URI: '%s'. Must be a valid HTTPS URL.", $invalid_uri));

        JwksUri::fromString($invalid_uri);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideInvalidUris(): \Iterator
    {
        yield 'empty string' => [''];
        yield 'invalid scheme' => ['ftp://example.com/jwks.json'];
        yield 'no scheme' => ['example.com/jwks.json'];
        yield 'no host' => ['https:///jwks.json'];
        yield 'malformed url' => ['https://'];
        yield 'invalid characters' => ['https://exam ple.com/jwks.json'];
    }
}
