<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksCacheInvalidated;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Caches JWKS with TTL
 */
final readonly class JwksCache
{
    private const string CACHE_KEY_PREFIX = 'jwks';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private int $default_ttl_seconds = 3600, // 1 hour default
        private LoggerInterface $logger = new NullLogger(),
        private EventDispatcherInterface|null $eventDispatcher = null,
    ) {
    }

    /**
     * Get cached JWKS for the given URI
     */
    public function get(JwksUri $uri): JsonWebKeySet|null
    {
        $cache_key = $this->generateCacheKey($uri);

        $this->logger->debug('Checking JWKS cache', [
            'uri' => $uri->toString(),
            'cache_key' => $cache_key,
        ]);

        $item = $this->cache->getItem($cache_key);

        if (! $item->isHit()) {
            $this->logger->debug('JWKS cache miss', ['uri' => $uri->toString()]);
            return null;
        }

        $cached_data = $item->get();

        if (! \is_array($cached_data)) {
            $this->logger->warning('Invalid cached JWKS data, removing from cache', [
                'uri' => $uri->toString(),
            ]);
            $this->cache->deleteItem($cache_key);
            return null;
        }

        try {
            $jwks = JsonWebKeySet::fromArray($cached_data);
            $this->logger->debug('JWKS cache hit', [
                'uri' => $uri->toString(),
                'key_count' => $jwks->count(),
            ]);
            return $jwks;
        } catch (\Exception $exception) {
            $this->logger->error('Failed to deserialize cached JWKS, removing from cache', [
                'uri' => $uri->toString(),
                'error' => $exception->getMessage(),
            ]);
            $this->cache->deleteItem($cache_key);
            return null;
        }
    }

    /**
     * Store JWKS in cache with TTL
     */
    public function store(JwksUri $uri, JsonWebKeySet $jwks, int|null $ttl_seconds = null): void
    {
        $cache_key = $this->generateCacheKey($uri);
        $ttl = $ttl_seconds ?? $this->default_ttl_seconds;

        $this->logger->debug('Storing JWKS in cache', [
            'uri' => $uri->toString(),
            'cache_key' => $cache_key,
            'ttl' => $ttl,
            'key_count' => $jwks->count(),
        ]);

        $item = $this->cache->getItem($cache_key);
        $item->set($jwks->toArray());
        $item->expiresAfter($ttl);

        $this->cache->save($item);
    }

    /**
     * Clear cached JWKS for the given URI
     */
    public function clear(JwksUri $uri): void
    {
        $cache_key = $this->generateCacheKey($uri);

        $this->logger->debug('Clearing JWKS cache', [
            'uri' => $uri->toString(),
            'cache_key' => $cache_key,
        ]);

        $this->cache->deleteItem($cache_key);

        // Dispatch cache invalidation event
        $this->eventDispatcher?->dispatch(new JwksCacheInvalidated(
            jwksUri: $uri->toString(),
            reason: 'manual_cache_clear',
            triggeredBy: 'cache_clear_method',
        ));
    }

    /**
     * Clear all JWKS cache entries
     */
    public function clearAll(): void
    {
        $this->logger->debug('Clearing all JWKS cache entries');

        // Note: This is a simple implementation. In production, you might want
        // to use cache tagging for more efficient bulk operations
        $this->cache->clear();

        // Dispatch cache invalidation event
        $this->eventDispatcher?->dispatch(new JwksCacheInvalidated(
            jwksUri: 'all',
            reason: 'cache_clear_all',
            triggeredBy: 'clear_all_method',
        ));
    }

    /**
     * Invalidate cache on verification failure (smart invalidation)
     */
    public function invalidateOnFailure(JwksUri $uri, string $reason): void
    {
        $cache_key = $this->generateCacheKey($uri);

        $this->logger->info('Invalidating JWKS cache due to failure', [
            'uri' => $uri->toString(),
            'reason' => $reason,
        ]);

        $this->cache->deleteItem($cache_key);

        // Dispatch cache invalidation event
        $this->eventDispatcher?->dispatch(new JwksCacheInvalidated(
            jwksUri: $uri->toString(),
            reason: $reason,
            triggeredBy: 'failure_based_invalidation',
        ));
    }

    /**
     * Force refresh cache (invalidate and fetch new)
     */
    public function forceRefresh(JwksUri $uri): void
    {
        $this->logger->info('Force refreshing JWKS cache', [
            'uri' => $uri->toString(),
        ]);

        $this->clear($uri);

        // Dispatch cache invalidation event
        $this->eventDispatcher?->dispatch(new JwksCacheInvalidated(
            jwksUri: $uri->toString(),
            reason: 'force_refresh',
            triggeredBy: 'force_refresh_method',
        ));
    }

    /**
     * Generate a consistent cache key for the given URI
     */
    private function generateCacheKey(JwksUri $uri): string
    {
        return self::CACHE_KEY_PREFIX . '.' . \hash('sha256', $uri->toString());
    }
}
