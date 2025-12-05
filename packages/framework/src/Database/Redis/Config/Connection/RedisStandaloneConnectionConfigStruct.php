<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis\Config\Connection;

use PhoneBurner\Pinch\Component\Configuration\Exception\InvalidConfiguration;
use PhoneBurner\Pinch\Component\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\Pinch\Component\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\Pinch\Filesystem\Domain\TlsContextOptions;
use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisAuthenticationCredentials;
use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisConnectionConfigStruct;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCompatibleType;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisConnectionType;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;

final class RedisStandaloneConnectionConfigStruct implements RedisConnectionConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    // phpcs:disable
    public RedisConnectionType $connection_type {
        get => RedisConnectionType::Standalone;
    }
    // phpcs:enable

    public function __construct(
        public string $host,
        public int $port = 6379,
        public readonly RedisAuthenticationCredentials|null $credentials = null,
        public readonly TimeInterval|null $connection_timeout = new TimeInterval(),
        public readonly TimeInterval|null $read_timeout = new TimeInterval(),
        public readonly TimeInterval|null $retry_interval = new TimeInterval(),
        public readonly bool $persistent = true,
        public readonly bool|TlsContextOptions $tls = false,
        public readonly array|null $backoff_options = null,
        public readonly RedisCompatibleType $compatible_type = RedisCompatibleType::Redis,
    ) {
        ($port > 0 && $port <= 65535) || throw new InvalidConfiguration('Redis Config Invalid: Port');
        $host !== '' || throw new InvalidConfiguration('Redis Config Invalid: Host');
    }

    public function __serialize(): array
    {
        return \get_mangled_object_vars($this);
    }
}
