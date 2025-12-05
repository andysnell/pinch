<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis\Config;

use PhoneBurner\Pinch\Component\Configuration\ConfigStruct;
use PhoneBurner\Pinch\Filesystem\Domain\TlsContextOptions;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisCompatibleType;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisConnectionType;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;

interface RedisConnectionConfigStruct extends ConfigStruct
{
        // phpcs:disable
        public RedisAuthenticationCredentials|null $credentials { get; }
        public TimeInterval|null $connection_timeout { get;}
        public TimeInterval|null $read_timeout { get;}
        public bool $persistent { get;}
        public bool|TlsContextOptions $tls { get;}
        public RedisCompatibleType $compatible_type { get;}
        public RedisConnectionType $connection_type { get;}
        // phpcs:enable
}
