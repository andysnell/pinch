<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\RedisBridge;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\RateLimit\RateLimitState;
use PhoneBurner\Pinch\Component\RateLimit\RateLimitStateStore;
use PhoneBurner\Pinch\Component\RateLimit\RateLimitStateTimestamps;
use PhoneBurner\Pinch\Time\Clock\Clock;

class RedisRateLimitStateStore implements RateLimitStateStore
{
    public const int CLOCK_DRIFT_ALLOWANCE = 3;

    public const string DEFAULT_PREFIX = 'rate_limit';

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
    public function get(CacheKey|string $key): RateLimitState
    {
        return $this->set($key, 0);
    }

    public function set(CacheKey|string $key, int $count = 1): RateLimitState
    {
        $key = $key instanceof CacheKey ? $key : CacheKey::make($key);
        $timestamps = new RateLimitStateTimestamps($this->clock->now());

        $second_key = $this->key($key, 's', $timestamps->second->start);
        $minute_key = $this->key($key, 'm', $timestamps->minute->start);
        $hour_key = $this->key($key, 'h', $timestamps->hour->start);
        $day_key = $this->key($key, 'd', $timestamps->day->start);

        // Note: the commands within the multi/exec block are executed in a single atomic operation
        [$second, $minute, $hour, $day] = $this->redis->multi()
            ->incr($second_key, $count)
            ->incr($minute_key, $count)
            ->incr($hour_key, $count)
            ->incr($day_key, $count)
            ->expireAt($second_key, $timestamps->second->reset + self::CLOCK_DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->expireAt($minute_key, $timestamps->minute->reset + self::CLOCK_DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->expireAt($hour_key, $timestamps->hour->reset + self::CLOCK_DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->expireAt($day_key, $timestamps->day->reset + self::CLOCK_DRIFT_ALLOWANCE, self::EXPIRE_MODE)
            ->exec();

        return new RateLimitState($timestamps, $second, $minute, $hour, $day);
    }

    private function key(CacheKey $key, string $period, int $timestamp): string
    {
        return \sprintf('%s.%s.%s.%s', $this->prefix, $key->normalized, $period, $timestamp);
    }
}
