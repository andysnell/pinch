<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis;

enum RedisConnectionType: string
{
    case Standalone = 'standalone';
    case Cluster = 'cluster';
    case Sentinel = 'sentinel';
    case Array = 'array';
}
