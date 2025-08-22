<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKeySet;
use PhoneBurner\Pinch\Framework\Cryptography\Exception\JwksFetchFailure;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * High-level resolver that combines JWKS fetching and caching
 */
final readonly class JwksResolver
{
    public function __construct(
        private JwksFetcher $fetcher,
        private JwksCache $cache,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * Resolve JWKS from cache or fetch from remote URI
     *
     * @throws JwksFetchFailure
     * @throws InvalidJsonWebKeySet
     */
    public function resolve(JwksUri $uri): JsonWebKeySet
    {
        $this->logger->debug('Resolving JWKS', ['uri' => $uri->toString()]);

        // Try cache first
        $cached_jwks = $this->cache->get($uri);
        if ($cached_jwks !== null) {
            $this->logger->debug('Using cached JWKS', [
                'uri' => $uri->toString(),
                'key_count' => $cached_jwks->count(),
            ]);
            return $cached_jwks;
        }

        // Cache miss - fetch from remote
        $this->logger->debug('Cache miss, fetching JWKS from remote', ['uri' => $uri->toString()]);

        $jwks = $this->fetcher->fetch($uri);

        // Store in cache for future requests
        $this->cache->store($uri, $jwks);

        $this->logger->info('Successfully resolved and cached JWKS', [
            'uri' => $uri->toString(),
            'key_count' => $jwks->count(),
        ]);

        return $jwks;
    }

    /**
     * Find a specific key by ID, resolving JWKS if needed
     *
     * @throws JwksFetchFailure
     * @throws InvalidJsonWebKeySet
     */
    public function findKeyById(JwksUri $uri, string $key_id): JsonWebKey|null
    {
        $this->logger->debug('Finding key by ID', [
            'uri' => $uri->toString(),
            'key_id' => $key_id,
        ]);

        $jwks = $this->resolve($uri);
        $key = $jwks->findByKeyId($key_id);

        if ($key === null) {
            $this->logger->warning('Key not found in JWKS', [
                'uri' => $uri->toString(),
                'key_id' => $key_id,
                'available_keys' => \array_map(
                    static fn(JsonWebKey $k): string => $k->key_id,
                    $jwks->keys,
                ),
            ]);
        } else {
            $this->logger->debug('Found key by ID', [
                'uri' => $uri->toString(),
                'key_id' => $key_id,
                'key_type' => $key->key_type,
                'algorithm' => $key->algorithm,
            ]);
        }

        return $key;
    }

    /**
     * Force refresh JWKS from remote (bypass cache)
     *
     * @throws JwksFetchFailure
     * @throws InvalidJsonWebKeySet
     */
    public function refresh(JwksUri $uri): JsonWebKeySet
    {
        $this->logger->debug('Force refreshing JWKS from remote', ['uri' => $uri->toString()]);

        // Clear cache first
        $this->cache->clear($uri);

        // Fetch fresh copy
        $jwks = $this->fetcher->fetch($uri);

        // Store in cache
        $this->cache->store($uri, $jwks);

        $this->logger->info('Successfully refreshed JWKS', [
            'uri' => $uri->toString(),
            'key_count' => $jwks->count(),
        ]);

        return $jwks;
    }
}
