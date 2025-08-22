<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJwksUri;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\JwksFetchFailure;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\CertificatePinnedJwksFetcher;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksUri;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;

final class CertificatePinnedJwksFetcherTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;
    private RequestFactoryInterface&MockObject $requestFactory;
    private NullLogger $logger;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->logger = new NullLogger();
    }

    #[Test]
    public function rejectsNonHttpsUrls(): void
    {
        // JwksUri already validates HTTPS, so we can't even create a non-HTTPS URI
        $this->expectException(InvalidJwksUri::class);
        $this->expectExceptionMessage('JWKS URI must use HTTPS for security');

        JwksUri::fromString('http://insecure.example.com/jwks.json');
    }

    #[Test]
    public function fetchesJwksSuccessfully(): void
    {
        $jwksJson = '{"keys":[{"kty":"RSA","use":"sig","alg":"RS256","kid":"key1","n":"test","e":"AQAB"}]}';

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($jwksJson);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn($stream);

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'https://example.com/jwks.json')
            ->willReturn($request);

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $fetcher = new CertificatePinnedJwksFetcher(
            $this->httpClient,
            $this->requestFactory,
            [],
            $this->logger,
        );

        $uri = JwksUri::fromString('https://example.com/jwks.json');
        $result = $fetcher->fetch($uri);

        self::assertSame(1, $result->count());
    }

    #[Test]
    public function handlesHttpErrors(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getReasonPhrase')->willReturn('Not Found');

        $this->requestFactory
            ->method('createRequest')
            ->willReturn($request);

        $this->httpClient
            ->method('sendRequest')
            ->willReturn($response);

        $fetcher = new CertificatePinnedJwksFetcher(
            $this->httpClient,
            $this->requestFactory,
            [],
            $this->logger,
        );

        $uri = JwksUri::fromString('https://example.com/jwks.json');

        $this->expectException(JwksFetchFailure::class);
        $this->expectExceptionMessage("Failed to fetch JWKS from 'https://example.com/jwks.json'. HTTP 404: Not Found");

        $fetcher->fetch($uri);
    }

    #[Test]
    public function addCertificatePin(): void
    {
        $fetcher = new CertificatePinnedJwksFetcher(
            $this->httpClient,
            $this->requestFactory,
            [],
            $this->logger,
        );

        $newFetcher = $fetcher->addCertificatePin('example.com', 'sha256:ABC123');

        self::assertNotSame($fetcher, $newFetcher);
        self::assertInstanceOf(CertificatePinnedJwksFetcher::class, $newFetcher);
    }

    #[Test]
    public function withAwsCognitoPins(): void
    {
        $fetcher = CertificatePinnedJwksFetcher::withAwsCognitoPins(
            $this->httpClient,
            $this->requestFactory,
            $this->logger,
        );

        self::assertInstanceOf(CertificatePinnedJwksFetcher::class, $fetcher);
    }

    #[Test]
    public function handlesEmptyResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn($stream);

        $this->requestFactory
            ->method('createRequest')
            ->willReturn($request);

        $this->httpClient
            ->method('sendRequest')
            ->willReturn($response);

        $fetcher = new CertificatePinnedJwksFetcher(
            $this->httpClient,
            $this->requestFactory,
            [],
            $this->logger,
        );

        $uri = JwksUri::fromString('https://example.com/jwks.json');

        $this->expectException(JwksFetchFailure::class);
        $this->expectExceptionMessage("Failed to fetch JWKS from 'https://example.com/jwks.json'. HTTP 200: Empty response body");

        $fetcher->fetch($uri);
    }
}
