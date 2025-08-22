<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks;

use PhoneBurner\Pinch\Component\HttpClient\HttpClient;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKeySet;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\JwksFetchFailure;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksFetcher;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksUri;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class JwksFetcherTest extends TestCase
{
    private HttpClient&MockObject $http_client;
    private RequestFactoryInterface&MockObject $request_factory;
    private LoggerInterface&MockObject $logger;
    private JwksFetcher $fetcher;

    protected function setUp(): void
    {
        $this->http_client = $this->createMock(HttpClient::class);
        $this->request_factory = $this->createMock(RequestFactoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->fetcher = new JwksFetcher(
            $this->http_client,
            $this->request_factory,
            $this->logger,
        );
    }

    #[Test]
    public function successfullyFetchesJwks(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');
        $jwks_json = '{"keys":[{"kid":"key1","kty":"RSA","use":"sig","alg":"RS256","n":"modulus","e":"AQAB"}]}';

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($jwks_json);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn($stream);

        $this->request_factory
            ->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'https://example.com/.well-known/jwks.json')
            ->willReturn($request);

        $this->http_client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->logger
            ->expects(self::atLeastOnce())
            ->method('debug');

        $jwks = $this->fetcher->fetch($uri);

        self::assertCount(1, $jwks->keys);
        self::assertSame('key1', $jwks->keys[0]->key_id);
    }

    #[Test]
    public function throwsExceptionForHttpError(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getReasonPhrase')->willReturn('Not Found');

        $this->request_factory->method('createRequest')->willReturn($request);
        $this->http_client->method('sendRequest')->willReturn($response);

        $this->expectException(JwksFetchFailure::class);
        $this->expectExceptionMessage("Failed to fetch JWKS from 'https://example.com/.well-known/jwks.json'. HTTP 404: Not Found");

        $this->fetcher->fetch($uri);
    }

    #[Test]
    public function throwsExceptionForNetworkError(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $network_exception = new class extends RuntimeException implements ClientExceptionInterface {
        };

        $this->request_factory->method('createRequest')->willReturn($request);
        $this->http_client->method('sendRequest')->willThrowException($network_exception);

        $this->expectException(JwksFetchFailure::class);
        $this->expectExceptionMessage("Network error while fetching JWKS from 'https://example.com/.well-known/jwks.json'");

        $this->fetcher->fetch($uri);
    }

    #[Test]
    public function throwsExceptionForEmptyResponse(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn($stream);

        $this->request_factory->method('createRequest')->willReturn($request);
        $this->http_client->method('sendRequest')->willReturn($response);

        $this->expectException(JwksFetchFailure::class);
        $this->expectExceptionMessage("Failed to fetch JWKS from 'https://example.com/.well-known/jwks.json'. HTTP 200: Empty response body");

        $this->fetcher->fetch($uri);
    }

    #[Test]
    public function throwsExceptionForInvalidJson(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('{"invalid": json}');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn($stream);

        $this->request_factory->method('createRequest')->willReturn($request);
        $this->http_client->method('sendRequest')->willReturn($response);

        $this->expectException(InvalidJsonWebKeySet::class);
        $this->expectExceptionMessage('Invalid JSON in JWKS response:');

        $this->fetcher->fetch($uri);
    }
}
