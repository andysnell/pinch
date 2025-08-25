<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\RedisBridge;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitState;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitStateStore;
use PhoneBurner\Pinch\Time\Clock\Clock;

use const PhoneBurner\Pinch\Time\SECONDS_IN_DAY;
use const PhoneBurner\Pinch\Time\SECONDS_IN_HOUR;
use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

class RedisRequestRateLimitStateStore implements RequestRateLimitStateStore
{
    public const int DRIFT_ALLOWANCE = 3;

    public const string DEFAULT_PREFIX = 'throttle';

    /**
     * Only set an expiration on a key, if it does not already have an expiration.
     * Since we're always setting the same expiration values, we can skip the extra
     * writes and dict updates this way.
     */
    private const string EXPIRE_MODE = 'NX';

    public function __construct(
        private readonly \Redis $redis,
        private readonly Clock $clock,
        private readonly string $prefix = self::DEFAULT_PREFIX,
    ) {
    }

    /**
     * Fetching the current state is equivalent to incrementing the state by 0.
     * The benefit is not having to deal with casting string|false return values
     * with the minor downside of creating the keys which do expire. For use cases
     * where the expected state is almost always 0, it would be best to re-implement.
     */
    public function get(CacheKey|string $key): RequestRateLimitState
    {
        return $this->set($key, 0);
    }

    public function set(CacheKey|string $key, int $count = 1): RequestRateLimitState
    {
        $key = $key instanceof CacheKey ? $key : CacheKey::make($key);
        $datetime = $this->clock->now();

        $timestamp = $datetime->getTimestamp();

        $second_timestamp = $timestamp;
        $second_key = $this->key($key, 's', $second_timestamp);

        $minute_timestamp = \intdiv($timestamp, SECONDS_IN_MINUTE) * SECONDS_IN_MINUTE;
        $minute_key = $this->key($key, 'm', $minute_timestamp);

        $hour_timestamp = \intdiv($timestamp, SECONDS_IN_HOUR) * SECONDS_IN_HOUR;
        $hour_key = $this->key($key, 'h', $hour_timestamp);

        $day_timestamp = \intdiv($timestamp, SECONDS_IN_DAY) * SECONDS_IN_DAY;
        $day_key = $this->key($key, 'd', $day_timestamp);

        // Note: the commands within the multi/exec block are executed in a single atomic operation
        [$second, $minute, $hour, $day] = $this->redis->multi()
            ->incr($second_key, $count)
            ->incr($minute_key, $count)
            ->incr($hour_key, $count)
            ->incr($day_key, $count)
            ->expireAt($second_key, $second_timestamp + 1 + self::DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->expireAt($minute_key, $minute_timestamp + SECONDS_IN_MINUTE + self::DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->expireAt($hour_key, $hour_timestamp + SECONDS_IN_HOUR + self::DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->expireAt($day_key, $day_timestamp + SECONDS_IN_DAY + self::DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->exec();

        return new RequestRateLimitState($datetime, $second, $minute, $hour, $day);
    }

    private function key(CacheKey $key, string $period, int $timestamp): string
    {
        return \sprintf('%s.%s.%s.%s', $this->prefix, $key->normalized, $period, $timestamp);
    }
}
