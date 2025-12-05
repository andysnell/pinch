<?php

declare(strict_types=1);

use PhoneBurner\NumberManagement\Util\Lock\ValkeyStore;
use PhoneBurner\Pinch\Component\Cache\CacheDriver;
use PhoneBurner\Pinch\String\Serialization\Serializer;

use function PhoneBurner\Pinch\Framework\env;
use function PhoneBurner\Pinch\Framework\stage;

return [
    'cache' => [
        'lock' => [
            // If set to true, resource keys will be cached in memory for the life of the process
            // (or technically the container). This allows for sharing access without directly passing
            // the key around. However, this could have unexpected consequences for long-running processes.
            'enable_process_key_cache' => false,
            'store' => ValkeyStore::class,
            'options' => [
                'driver' => stage('valkey-cluster', 'valkey-standalone'),
            ],
        ],
        'drivers' => [
            CacheDriver::Remote->value => [
                'serializer' => env('PINCH_REMOTE_CACHE_SERIALIZER', Serializer::Igbinary, Serializer::Php),
            ],
            CacheDriver::File->value => [

            ],
            CacheDriver::Memory->value => [

            ],
        ],
    ],
];
