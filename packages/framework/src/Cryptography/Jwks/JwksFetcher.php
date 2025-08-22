<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Component\HttpClient\HttpClient;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKeySet;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\JwksFetchFailure;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Fetches JWKS from remote URLs
 */
final readonly class JwksFetcher
{
    public function __construct(
        private HttpClient $http_client,
        private RequestFactoryInterface $request_factory,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * Fetch JWKS from the given URI
     *
     * @throws JwksFetchFailure
     * @throws InvalidJsonWebKeySet
     */
    public function fetch(JwksUri $uri): JsonWebKeySet
    {
        $this->logger->debug('Fetching JWKS from URI', ['uri' => $uri->toString()]);

        try {
            $request = $this->request_factory->createRequest('GET', $uri->toString());
            $request = $request->withHeader('Accept', 'application/json');
            $request = $request->withHeader('User-Agent', 'Pinch-Framework-JWKS-Fetcher/1.0');

            $response = $this->http_client->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                throw JwksFetchFailure::fromHttpError(
                    $uri->toString(),
                    $response->getStatusCode(),
                    $response->getReasonPhrase(),
                );
            }

            $content_type = $response->getHeaderLine('Content-Type');
            if ($content_type !== '' && ! \str_contains($content_type, 'application/json')) {
                $this->logger->warning('JWKS endpoint returned non-JSON content type', [
                    'uri' => $uri->toString(),
                    'content_type' => $content_type,
                ]);
            }

            $body = $response->getBody()->getContents();

            if ($body === '') {
                throw JwksFetchFailure::fromHttpError($uri->toString(), 200, 'Empty response body');
            }

            $this->logger->debug('Successfully fetched JWKS', [
                'uri' => $uri->toString(),
                'response_size' => \strlen($body),
            ]);

            return JsonWebKeySet::fromJson($body);
        } catch (ClientExceptionInterface $exception) {
            $this->logger->error('HTTP client error while fetching JWKS', [
                'uri' => $uri->toString(),
                'error' => $exception->getMessage(),
            ]);

            throw JwksFetchFailure::fromNetworkError($uri->toString(), $exception);
        } catch (\JsonException $exception) {
            $this->logger->error('Invalid JSON in JWKS response', [
                'uri' => $uri->toString(),
                'error' => $exception->getMessage(),
            ]);

            throw InvalidJsonWebKeySet::fromInvalidJson($exception->getMessage());
        }
    }
}
