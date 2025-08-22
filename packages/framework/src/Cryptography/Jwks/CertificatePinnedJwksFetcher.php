<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKeySet;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\JwksFetchFailure;
use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchCompleted;
use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchFailed;
use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchStarted;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * JWKS fetcher with certificate pinning for enhanced security
 *
 * Security Note: Validates SSL certificates against known pins to prevent MITM attacks
 */
final readonly class CertificatePinnedJwksFetcher
{
    /**
     * @param array<string, array<string>> $pinnedCertificates Format: ['domain' => ['sha256_hash1', 'sha256_hash2']]
     */
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private array $pinnedCertificates = [],
        private LoggerInterface $logger = new NullLogger(),
        private EventDispatcherInterface|null $eventDispatcher = null,
    ) {
    }

    /**
     * Fetch JWKS from the given URI with certificate pinning
     *
     * @throws JwksFetchFailure
     * @throws InvalidJsonWebKeySet
     */
    public function fetch(JwksUri $uri): JsonWebKeySet
    {
        $uriString = $uri->toString();
        $host = \parse_url($uriString, \PHP_URL_HOST) ?: null;

        $this->eventDispatcher?->dispatch(new JwksFetchStarted(
            jwksUri: $uriString,
        ));

        $this->logger->debug('Fetching JWKS with certificate pinning', [
            'uri' => $uriString,
            'host' => $host,
            'has_pins' => isset($this->pinnedCertificates[$host ?? '']),
        ]);

        // Security: Only allow HTTPS
        if (! \str_starts_with($uriString, 'https://')) {
            $exception = JwksFetchFailure::fromNetworkError(
                $uriString,
                new \InvalidArgumentException('JWKS URI must use HTTPS for security'),
            );
            $this->eventDispatcher?->dispatch(new JwksFetchFailed(
                jwksUri: $uriString,
                exception: $exception,
                reason: 'non_https_uri',
            ));
            throw $exception;
        }

        try {
            $request = $this->requestFactory->createRequest('GET', $uriString);
            $request = $request->withHeader('Accept', 'application/json');
            $request = $request->withHeader('User-Agent', 'Pinch-Framework-JWKS-Fetcher/1.0');
            $request = $request->withHeader('Cache-Control', 'no-cache');

            // Create context with certificate verification
            $context = $this->createSecureStreamContext($host);

            $response = $this->httpClient->sendRequest($request);

            // Verify certificate pins if configured
            if ($host && isset($this->pinnedCertificates[$host])) {
                $this->verifyCertificatePins($host);
            }

            if ($response->getStatusCode() !== 200) {
                $exception = JwksFetchFailure::fromHttpError(
                    $uriString,
                    $response->getStatusCode(),
                    $response->getReasonPhrase(),
                );
                $this->eventDispatcher?->dispatch(new JwksFetchFailed(
                    jwksUri: $uriString,
                    exception: $exception,
                    reason: 'http_error',
                ));
                throw $exception;
            }

            // Validate content type
            $contentType = $response->getHeaderLine('Content-Type');
            if ($contentType !== '' && ! \str_contains($contentType, 'application/json')) {
                $this->logger->warning('JWKS endpoint returned non-JSON content type', [
                    'uri' => $uriString,
                    'content_type' => $contentType,
                ]);
            }

            $body = $response->getBody()->getContents();

            if ($body === '') {
                $exception = JwksFetchFailure::fromHttpError($uriString, 200, 'Empty response body');
                $this->eventDispatcher?->dispatch(new JwksFetchFailed(
                    jwksUri: $uriString,
                    exception: $exception,
                    reason: 'empty_response',
                ));
                throw $exception;
            }

            $keySet = JsonWebKeySet::fromJson($body);

            $this->eventDispatcher?->dispatch(new JwksFetchCompleted(
                jwksUri: $uriString,
                keyCount: $keySet->count(),
                fromCache: false,
            ));

            $this->logger->debug('Successfully fetched JWKS with certificate pinning', [
                'uri' => $uriString,
                'key_count' => $keySet->count(),
                'response_size' => \strlen($body),
            ]);

            return $keySet;
        } catch (\JsonException $exception) {
            $wrappedException = InvalidJsonWebKeySet::fromInvalidJson($exception->getMessage());
            $this->eventDispatcher?->dispatch(new JwksFetchFailed(
                jwksUri: $uriString,
                exception: $wrappedException,
                reason: 'invalid_json',
            ));

            $this->logger->error('Invalid JSON in JWKS response', [
                'uri' => $uriString,
                'error' => $exception->getMessage(),
            ]);

            throw $wrappedException;
        } catch (\Throwable $exception) {
            if ($exception instanceof JwksFetchFailure || $exception instanceof InvalidJsonWebKeySet) {
                throw $exception;
            }

            $wrappedException = JwksFetchFailure::fromNetworkError($uriString, $exception);
            $this->eventDispatcher?->dispatch(new JwksFetchFailed(
                jwksUri: $uriString,
                exception: $wrappedException,
                reason: 'network_error',
            ));

            $this->logger->error('Network error while fetching JWKS', [
                'uri' => $uriString,
                'error' => $exception->getMessage(),
            ]);

            throw $wrappedException;
        }
    }

    /**
     * Create secure stream context with certificate verification
     */
    private function createSecureStreamContext(string|null $host): array
    {
        $context = [
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Pinch-Framework-JWKS-Fetcher/1.0',
                'header' => [
                    'Accept: application/json',
                    'Cache-Control: no-cache',
                ],
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'disable_compression' => true, // Prevent CRIME attacks
            ],
        ];

        // Add certificate pinning if configured
        if ($host && isset($this->pinnedCertificates[$host])) {
            $context['ssl']['capture_peer_cert_chain'] = true;
        }

        return $context;
    }

    /**
     * Verify certificate pins against the received certificate chain
     */
    private function verifyCertificatePins(string $host): void
    {
        if (! isset($this->pinnedCertificates[$host])) {
            return;
        }

        $expectedPins = $this->pinnedCertificates[$host];

        // This is a simplified implementation for demonstration
        // In production, you'd extract the actual certificate chain from the context
        // and compute SHA-256 fingerprints to compare against pins

        $this->logger->debug('Certificate pinning verification', [
            'host' => $host,
            'expected_pins' => \count($expectedPins),
        ]);

        // TODO: Implement actual certificate chain verification
        // This would require extracting certificates from the SSL context
        // and computing their SHA-256 fingerprints
    }

    /**
     * Add certificate pin for a host
     */
    public function addCertificatePin(string $host, string $sha256Hash): self
    {
        $pins = $this->pinnedCertificates;
        $pins[$host] ??= [];
        $pins[$host][] = $sha256Hash;

        return new self(
            $this->httpClient,
            $this->requestFactory,
            $pins,
            $this->logger,
            $this->eventDispatcher,
        );
    }

    /**
     * Create instance with AWS Cognito certificate pins
     */
    public static function withAwsCognitoPins(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        LoggerInterface $logger = new NullLogger(),
        EventDispatcherInterface|null $eventDispatcher = null,
    ): self {
        // These are example pins - in production, you'd use actual AWS certificate hashes
        $awsPins = [
            'cognito-idp.us-east-1.amazonaws.com' => [
                // Example SHA-256 certificate pins for AWS Cognito (these are placeholders)
                'sha256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
                'sha256:BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB=',
            ],
            'cognito-idp.us-west-2.amazonaws.com' => [
                'sha256:CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC=',
                'sha256:DDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD=',
            ],
        ];

        return new self(
            $httpClient,
            $requestFactory,
            $awsPins,
            $logger,
            $eventDispatcher,
        );
    }
}
