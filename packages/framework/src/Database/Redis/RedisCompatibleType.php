<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis;

enum RedisCompatibleType: string
{
    /**
     * The original "Redis OSS" engine and AWS ElastiCache for Redis OSS
     */
    case Redis = 'redis';

    /**
     * The Valkey fork of Redis and AWS ElastiCache for Valkey
     */
    case Valkey = 'valkey';
}
