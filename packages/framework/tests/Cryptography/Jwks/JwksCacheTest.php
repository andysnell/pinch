<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JsonWebKeySet;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksCache;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksUri;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

final class JwksCacheTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cache_pool;
    private JwksCache $cache;

    protected function setUp(): void
    {
        $this->cache_pool = $this->createMock(CacheItemPoolInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->cache = new JwksCache($this->cache_pool, 3600, $logger);
    }

    #[Test]
    public function returnsNullForCacheMiss(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->method('isHit')->willReturn(false);

        $this->cache_pool
            ->expects(self::once())
            ->method('getItem')
            ->willReturn($cache_item);

        $result = $this->cache->get($uri);

        self::assertNull($result);
    }

    #[Test]
    public function returnsCachedJwksOnCacheHit(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');
        $jwks_data = [
            'keys' => [
                [
                    'kid' => 'key1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
            ],
        ];

        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->method('isHit')->willReturn(true);
        $cache_item->method('get')->willReturn($jwks_data);

        $this->cache_pool
            ->expects(self::once())
            ->method('getItem')
            ->willReturn($cache_item);

        $result = $this->cache->get($uri);

        self::assertNotNull($result);
        self::assertCount(1, $result->keys);
        self::assertSame('key1', $result->keys[0]->key_id);
    }

    #[Test]
    public function storesJwksInCache(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');
        $jwks = JsonWebKeySet::fromArray([
            'keys' => [
                [
                    'kid' => 'key1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
            ],
        ]);

        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->expects(self::once())->method('set')->with($jwks->toArray());
        $cache_item->expects(self::once())->method('expiresAfter')->with(3600);

        $this->cache_pool
            ->expects(self::once())
            ->method('getItem')
            ->willReturn($cache_item);

        $this->cache_pool
            ->expects(self::once())
            ->method('save')
            ->with($cache_item);

        $this->cache->store($uri, $jwks);
    }

    #[Test]
    public function storesJwksWithCustomTtl(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');
        $jwks = JsonWebKeySet::fromArray([
            'keys' => [
                [
                    'kid' => 'key1',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => 'modulus',
                    'e' => 'AQAB',
                ],
            ],
        ]);

        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->expects(self::once())->method('set')->with($jwks->toArray());
        $cache_item->expects(self::once())->method('expiresAfter')->with(1800);

        $this->cache_pool
            ->expects(self::once())
            ->method('getItem')
            ->willReturn($cache_item);

        $this->cache_pool
            ->expects(self::once())
            ->method('save')
            ->with($cache_item);

        $this->cache->store($uri, $jwks, 1800);
    }

    #[Test]
    public function clearsSpecificCacheEntry(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $this->cache_pool
            ->expects(self::once())
            ->method('deleteItem')
            ->with(self::stringContains('jwks.'));

        $this->cache->clear($uri);
    }

    #[Test]
    public function clearsAllCacheEntries(): void
    {
        $this->cache_pool
            ->expects(self::once())
            ->method('clear');

        $this->cache->clearAll();
    }

    #[Test]
    public function handlesInvalidCachedData(): void
    {
        $uri = JwksUri::fromString('https://example.com/.well-known/jwks.json');

        $cache_item = $this->createMock(CacheItemInterface::class);
        $cache_item->method('isHit')->willReturn(true);
        $cache_item->method('get')->willReturn('invalid-data');

        $this->cache_pool
            ->expects(self::once())
            ->method('getItem')
            ->willReturn($cache_item);

        $this->cache_pool
            ->expects(self::once())
            ->method('deleteItem');

        $result = $this->cache->get($uri);

        self::assertNull($result);
    }
}
