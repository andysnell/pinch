<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis\Config\Connection;

use PhoneBurner\Pinch\Component\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\Pinch\Component\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\Pinch\Filesystem\Domain\TlsContextOptions;
use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisAuthenticationCredentials;
use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisConnectionConfigStruct;
use PhoneBurner\Pinch\Framework\Database\Redis\Exception\InvalidRedisClusterConfiguration;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCompatibleType;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisConnectionType;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;
use PhoneBurner\Pinch\Uri\ParsedUrl;

final class RedisClusterConnectionConfigStruct implements RedisConnectionConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    // phpcs:disable
    public RedisConnectionType $connection_type {
        get => RedisConnectionType::Cluster;
    }
    // phpcs:enable

    /**
     * @param array<string>|null $seeds seeds for the Redis cluster. Can be `null` if the cluster is defined in the
     * PHP INI by the connection name. Otherwise, an array of hostname:port strings.
     * Example: ['127.0.0.1:7000', 'redis.abcdef.example.com:7001']
     * @param bool $persistent Enables the use of persistent connections when in the Http context. Other contexts
     * (e.g., cli, tests) will always use non-persistent connections.
     * @param bool|TlsContextOptions $tls Enables/configures connecting with TLS
     */
    public function __construct(
        public readonly array|null $seeds = [],
        public readonly RedisAuthenticationCredentials|null $credentials = null,
        public readonly TimeInterval|null $connection_timeout = new TimeInterval(),
        public readonly TimeInterval|null $read_timeout = new TimeInterval(),
        public readonly bool $persistent = true,
        public readonly bool|TlsContextOptions $tls = false,
        public readonly RedisCompatibleType $compatible_type = RedisCompatibleType::Redis,
    ) {
        if ($this->seeds === []) {
            throw new InvalidRedisClusterConfiguration('seed array must be null or non-empty array of host:port strings');
        }

        foreach ($this->seeds ?? [] as $seed) {
            $parsed = new ParsedUrl(...\parse_url($seed) ?: []);

            if (! \in_array($parsed->scheme, [null, 'tls', 'ssl'], true)) {
                throw new InvalidRedisClusterConfiguration(
                    \sprintf('scheme must be null, "tls://" or "ssl://" (seed: %s)', $seed),
                );
            }

            if ($parsed->host === null || \filter_var($parsed->host, \FILTER_VALIDATE_DOMAIN) === false) {
                throw new InvalidRedisClusterConfiguration(
                    \sprintf('host must be a valid hostname or IP Address (seed: %s)', $seed),
                );
            }

            if ($parsed->port === null) { // range checked by parse_url()
                throw new InvalidRedisClusterConfiguration(
                    \sprintf('explicit port in range 0-65535 required (seed: %s)', $seed),
                );
            }

            foreach (['user', 'pass', 'path', 'query', 'fragment'] as $component) {
                if ($parsed->{$component} !== null) {
                    throw new InvalidRedisClusterConfiguration(
                        \sprintf('unsupported %s component (seed: %s)', $seed, $component),
                    );
                }
            }
        }
    }

    public static function importSeeds(mixed $seeds): array|null
    {
        return match (true) {
            $seeds === null, $seeds === '' => null,
            \is_string($seeds) => \explode(',', $seeds),
            default => throw new InvalidRedisClusterConfiguration('Cannot import seeds from invalid type: ' . \gettype($seeds)),
        };
    }

    public function __serialize(): array
    {
        return \get_mangled_object_vars($this);
    }
}
