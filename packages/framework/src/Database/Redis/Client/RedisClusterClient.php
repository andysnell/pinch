<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis\Client;

use PhoneBurner\Pinch\Filesystem\Domain\TlsContextOptions;
use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisAuthenticationCredentials;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCommon;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCompatibleType;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisConnectionType;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;

class RedisClusterClient extends \RedisCluster implements RedisCommon
{
    // phpcs:disable
    public RedisConnectionType $connection_type {
        get => RedisConnectionType::Cluster;
    }
    // phpcs:enable

    public function __construct(
        string|null $name,
        array|null $seeds,
        RedisAuthenticationCredentials|null $credentials,
        TimeInterval|null $connection_timeout,
        TimeInterval|null $read_timeout,
        bool $persistent,
        bool|TlsContextOptions $tls,
        public readonly RedisCompatibleType $compatible_type,
    ) {
        parent::__construct(
            $name,
            $seeds,
            $connection_timeout->total_seconds ?? 0,
            $read_timeout->total_seconds ?? 0,
            $persistent,
            $credentials?->toArray(false),
            match ($tls) {
                true => ['verify_peer' => true],
                false => null,
                default => $tls->toArray(),
            },
        );
    }

    public function setex(string $key, int $expire, mixed $value): \Redis|\RedisCluster|bool
    {
        return parent::setex($key, $expire, $value);
    }

}
