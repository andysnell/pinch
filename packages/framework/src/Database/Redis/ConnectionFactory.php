<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis;

use PhoneBurner\Pinch\Framework\Database\Redis\Client\RedisClient;
use PhoneBurner\Pinch\Framework\Database\Redis\Client\RedisClusterClient;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisManager;

/**
 * Defines the factory class that can build the various kinds of Redis connections.
 */
interface ConnectionFactory
{
    public function connect(string $connection = RedisManager::DEFAULT): RedisClient|RedisClusterClient;
}
