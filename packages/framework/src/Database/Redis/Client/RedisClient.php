<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis\Client;

use PhoneBurner\Pinch\Filesystem\Domain\TlsContextOptions;
use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisAuthenticationCredentials;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCommon;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCompatibleType;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisConnectionType;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;

use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_MILLISECOND;

class RedisClient extends \Redis implements RedisCommon
{
    // phpcs:disable
    public RedisConnectionType $connection_type {
        get => RedisConnectionType::Standalone;
    }
    // phpcs:enable

    /**
     * @param array<string, mixed>|null $backoff
     */
    public function __construct(
        public private(set) string $name,
        string $host,
        int $port,
        RedisAuthenticationCredentials|null $credentials,
        TimeInterval|null $connection_timeout,
        TimeInterval|null $read_timeout,
        TimeInterval|null $retry_interval,
        bool $persistent,
        bool|TlsContextOptions $tls,
        public readonly RedisCompatibleType $compatible_type,
        array|null $backoff,
    ) {
        parent::__construct(\array_filter([
            'host' => $host ?: throw new \UnexpectedValueException('Host cannot be empty'),
            'port' => $port,
            'connectTimeout' => $connection_timeout?->total_seconds,
            'readTimeout' => $read_timeout?->total_seconds,
            'persistent' => $persistent,
            'retryInterval' => $retry_interval ? $retry_interval->microseconds * MICROSECONDS_IN_MILLISECOND : null,
            'auth' => $credentials?->toArray(false),
            'ssl' => match ($tls) {
                true => ['verify_peer' => true],
                false => null,
                default => $tls->toArray(),
            },
            'backoff' => $backoff,
        ], static fn (mixed $value): bool => $value !== null));
    }

    public function setex(string $key, int $expire, mixed $value): \Redis|\RedisCluster|bool
    {
        return parent::setex($key, $expire, $value);
    }
}
