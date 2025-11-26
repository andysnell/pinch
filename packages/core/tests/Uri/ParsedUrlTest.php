<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Uri;

use PhoneBurner\Pinch\Uri\ParsedUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParsedUrlTest extends TestCase
{
    #[Test]
    public function emptyObjectPath(): void
    {
        $parsed_url = new ParsedUrl();

        self::assertNull($parsed_url->scheme);
        self::assertNull($parsed_url->host);
        self::assertNull($parsed_url->port);
        self::assertNull($parsed_url->user);
        self::assertNull($parsed_url->pass);
        self::assertNull($parsed_url->path);
        self::assertNull($parsed_url->query);
        self::assertNull($parsed_url->fragment);

        self::assertSame('', (string)$parsed_url);
        self::assertEquals($parsed_url, \unserialize(\serialize($parsed_url)));
    }

    #[Test]
    public function happyObjectPath(): void
    {
        $scheme = 'https';
        $host = 'example.com';
        $port = 8080;
        $user = 'username';
        $pass = 'password';
        $path = '/path/to/resource';
        $query = 'foo=bar&baz=qux';
        $fragment = 'section';

        $parsed_url = new ParsedUrl(
            $scheme,
            $host,
            $port,
            $user,
            $pass,
            $path,
            $query,
            $fragment,
        );

        self::assertSame($scheme, $parsed_url->scheme);
        self::assertSame($host, $parsed_url->host);
        self::assertSame($port, $parsed_url->port);
        self::assertSame($user, $parsed_url->user);
        self::assertSame($pass, $parsed_url->pass);
        self::assertSame($path, $parsed_url->path);
        self::assertSame($query, $parsed_url->query);
        self::assertSame($fragment, $parsed_url->fragment);

        $url = 'https://username:password@example.com:8080/path/to/resource?foo=bar&baz=qux#section';
        self::assertSame($url, (string)$parsed_url);
        self::assertEquals($parsed_url, new ParsedUrl(...\parse_url($url)));
        self::assertEquals($parsed_url, \unserialize(\serialize($parsed_url)));
    }

    public static function providesUrlParsingTestCases(): \Generator
    {
        yield ['http://example.com', new ParsedUrl(
            scheme: 'http',
            host: 'example.com',
        )];

        yield ['https://api.example.com:443', new ParsedUrl(
            scheme: 'https',
            host: 'api.example.com',
            port: 443,
        )];

        yield ['https://example.com/api/v1/users', new ParsedUrl(
            scheme: 'https',
            host: 'example.com',
            path: '/api/v1/users',
        )];

        yield ['https://example.com?search=test&limit=10', new ParsedUrl(
            scheme: 'https',
            host: 'example.com',
            query: 'search=test&limit=10',
        )];

        yield ['https://example.com#introduction', new ParsedUrl(
            scheme: 'https',
            host: 'example.com',
            fragment: 'introduction',
        )];

        yield ['ftp://ftpuser:ftppass@ftp.example.com/files/document.pdf', new ParsedUrl(
            scheme: 'ftp',
            host: 'ftp.example.com',
            user: 'ftpuser',
            pass: 'ftppass',
            path: '/files/document.pdf',
        )];

        yield ['http://localhost:8000/api/endpoint?id=123', new ParsedUrl(
            scheme: 'http',
            host: 'localhost',
            port: 8000,
            path: '/api/endpoint',
            query: 'id=123',
        )];

        yield ['https://secure.example.com/dashboard#settings', new ParsedUrl(
            scheme: 'https',
            host: 'secure.example.com',
            path: '/dashboard',
            fragment: 'settings',
        )];

        yield ['ftp://files.example.com:21/public/downloads', new ParsedUrl(
            scheme: 'ftp',
            host: 'files.example.com',
            port: 21,
            path: '/public/downloads',
        )];

        yield ['https://api.example.com/search?q=test+query&category=books&sort=relevance&page=2', new ParsedUrl(
            scheme: 'https',
            host: 'api.example.com',
            path: '/search',
            query: 'q=test+query&category=books&sort=relevance&page=2',
        )];

        yield ['https://example.com/api/v2/users/123/profile', new ParsedUrl(
            scheme: 'https',
            host: 'example.com',
            path: '/api/v2/users/123/profile',
        )];

        yield ['https://apiuser:apipass@api.example.com:8443/v1/resources?filter=active&page=1#results', new ParsedUrl(
            scheme: 'https',
            host: 'api.example.com',
            port: 8443,
            user: 'apiuser',
            pass: 'apipass',
            path: '/v1/resources',
            query: 'filter=active&page=1',
            fragment: 'results',
        )];

        yield ['https://apiuser:apipass@api.example.com:8443/v1/resources?filter=active&page=1#results', new ParsedUrl(
            scheme: 'https',
            host: 'api.example.com',
            port: 8443,
            user: 'apiuser',
            pass: 'apipass',
            path: '/v1/resources',
            query: 'filter=active&page=1',
            fragment: 'results',
        )];
    }

    #[Test]
    #[DataProvider('providesUrlParsingTestCases')]
    public function toStringWithSchemeAndHostOnly(string $url, ParsedUrl $parsed_url): void
    {
        self::assertSame($url, (string)$parsed_url);
        self::assertEquals($parsed_url, new ParsedUrl(...\parse_url($url) ?: []));
    }

    #[Test]
    public function toStringWithPortOnly(): void
    {
        // Port without scheme/host should be ignored in reconstruction
        $parsed_url = new ParsedUrl(
            port: 8080,
        );

        self::assertSame('', (string)$parsed_url);
    }

    #[Test]
    public function toStringWithPathOnly(): void
    {
        $parsed_url = new ParsedUrl(
            path: '/relative/path',
        );

        self::assertSame('/relative/path', (string)$parsed_url);
    }

    #[Test]
    public function toStringWithQueryOnly(): void
    {
        $parsed_url = new ParsedUrl(
            query: 'standalone=query',
        );

        self::assertSame('?standalone=query', (string)$parsed_url);
    }

    #[Test]
    public function toStringWithFragmentOnly(): void
    {
        $parsed_url = new ParsedUrl(
            fragment: 'anchor',
        );

        self::assertSame('#anchor', (string)$parsed_url);
    }

    #[Test]
    public function serializationPreservesAllProperties(): void
    {
        $parsed_url = new ParsedUrl(
            scheme: 'https',
            host: 'example.com',
            port: 443,
            user: 'user',
            pass: 'pass',
            path: '/path',
            query: 'key=value',
            fragment: 'section',
        );

        $unserialized = \unserialize(\serialize($parsed_url));

        self::assertEquals($parsed_url, $unserialized);
        self::assertSame($parsed_url->scheme, $unserialized->scheme);
        self::assertSame($parsed_url->host, $unserialized->host);
        self::assertSame($parsed_url->port, $unserialized->port);
        self::assertSame($parsed_url->user, $unserialized->user);
        self::assertSame($parsed_url->pass, $unserialized->pass);
        self::assertSame($parsed_url->path, $unserialized->path);
        self::assertSame($parsed_url->query, $unserialized->query);
        self::assertSame($parsed_url->fragment, $unserialized->fragment);
    }

    #[Test]
    public function constructionWithEmptyParseUrlOutput(): void
    {
        $parsed = \parse_url('') ?: [];
        $parsed_url = new ParsedUrl(...$parsed);

        self::assertSame('', (string)$parsed_url);
    }
}
