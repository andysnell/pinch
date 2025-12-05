<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis;

/**
 * Common interface for \Redis and \RedisCluster implementations.
 *
 * This interface defines methods that are available on both \Redis and \RedisCluster
 * from the phpredis extension, allowing code to work with either connection type.
 *
 * @see https://github.com/phpredis/phpredis
 */
interface RedisCommon
{
    // =========================================================================
    // Connection & Configuration
    // =========================================================================

    public function close(): bool;

    public function echo(string $str): \Redis|\RedisCluster|string|false;

    public function setOption(int $option, mixed $value): bool;

    public function getOption(int $option): mixed;

    public function getLastError(): string|null;

    public function clearLastError(): bool;

    // =========================================================================
    // String Operations
    // =========================================================================

    public function get(string $key): mixed;

    public function set(string $key, mixed $value, mixed $options = null): \Redis|\RedisCluster|string|bool;

    public function setex(string $key, int $expire, mixed $value): \Redis|\RedisCluster|bool;

    public function psetex(string $key, int $milliseconds, mixed $value): \Redis|\RedisCluster|bool;

    public function setnx(string $key, mixed $value): \Redis|\RedisCluster|bool;

    public function getex(string $key, array $options = []): \Redis|\RedisCluster|string|bool;

    public function getDel(string $key): \Redis|\RedisCluster|string|bool;

    public function getset(string $key, mixed $value): \Redis|\RedisCluster|string|false;

    public function append(string $key, mixed $value): \Redis|\RedisCluster|int|false;

    public function getRange(string $key, int $start, int $end): \Redis|\RedisCluster|string|false;

    public function setRange(string $key, int $offset, string $value): \Redis|\RedisCluster|int|false;

    public function strlen(string $key): \Redis|\RedisCluster|int|false;

    public function incr(string $key, int $by = 1): \Redis|\RedisCluster|int|false;

    public function incrBy(string $key, int $value): \Redis|\RedisCluster|int|false;

    public function incrByFloat(string $key, float $value): \Redis|\RedisCluster|float|false;

    public function decr(string $key, int $by = 1): \Redis|\RedisCluster|int|false;

    public function decrBy(string $key, int $value): \Redis|\RedisCluster|int|false;

    /**
     * @param array<string> $keys
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function mget(array $keys): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed> $key_values
     */
    public function mset(array $key_values): \Redis|\RedisCluster|bool;

    /**
     * @param array<string, mixed> $key_values
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function msetnx(array $key_values): \Redis|\RedisCluster|array|false;

    // =========================================================================
    // Bit Operations
    // =========================================================================

    public function bitcount(
        string $key,
        int $start = 0,
        int $end = -1,
        bool $bybit = false,
    ): \Redis|\RedisCluster|int|false;

    public function bitop(
        string $operation,
        string $deskey,
        string $srckey,
        string ...$other_keys,
    ): \Redis|\RedisCluster|int|false;

    public function bitpos(
        string $key,
        bool $bit,
        int $start = 0,
        int $end = -1,
        bool $bybit = false,
    ): \Redis|\RedisCluster|int|false;

    public function getbit(string $key, int $idx): \Redis|\RedisCluster|int|false;

    public function setbit(string $key, int $idx, bool $value): \Redis|\RedisCluster|int|false;

    // =========================================================================
    // Key Operations
    // =========================================================================

    /**
     * @param array<string>|string $key
     * @return \Redis|\RedisCluster|int|false
     */
    public function del(array|string $key, string ...$other_keys): \Redis|\RedisCluster|int|false;

    public function exists(mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|bool;

    public function expire(string $key, int $timeout, string|null $mode = null): \Redis|\RedisCluster|bool;

    public function expireAt(string $key, int $timestamp, string|null $mode = null): \Redis|\RedisCluster|bool;

    public function expiretime(string $key): \Redis|\RedisCluster|int|false;

    public function pexpire(string $key, int $timeout, string|null $mode = null): \Redis|\RedisCluster|bool;

    public function pexpireAt(string $key, int $timestamp, string|null $mode = null): \Redis|\RedisCluster|bool;

    public function pexpiretime(string $key): \Redis|\RedisCluster|int|false;

    public function persist(string $key): \Redis|\RedisCluster|bool;

    public function ttl(string $key): \Redis|\RedisCluster|int|false;

    public function pttl(string $key): \Redis|\RedisCluster|int|false;

    public function type(string $key): \Redis|\RedisCluster|int|string|false;

    /**
     * @param array<string, mixed>|null $options
     */
    public function copy(string $src, string $dst, array|null $options = null): \Redis|\RedisCluster|bool;

    public function dump(string $key): \Redis|\RedisCluster|string|false;

    /**
     * @param array<string, mixed>|null $options
     */
    public function restore(
        string $key,
        int $ttl,
        string $value,
        array|null $options = null,
    ): \Redis|\RedisCluster|bool;

    public function rename(string $key, string $newkey): \Redis|\RedisCluster|bool;

    public function renamenx(string $key, string $newkey): \Redis|\RedisCluster|bool;

    public function touch(mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|false;

    /**
     * @param array<string>|string $key
     */
    public function unlink(array|string $key, string ...$other_keys): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<string>|false
     */
    public function keys(string $pattern): \Redis|\RedisCluster|array|false;

    public function randomkey(): \Redis|\RedisCluster|string|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function scan(
        int|null &$iterator,
        string|null $pattern = null,
        int $count = 0,
    ): \Redis|\RedisCluster|array|false;

    // =========================================================================
    // Hash Operations
    // =========================================================================

    public function hSet(string $key, mixed ...$fields_and_vals): \Redis|\RedisCluster|int|false;

    public function hGet(string $key, string $member): mixed;

    /**
     * @param array<string> $fields
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function hMget(string $key, array $fields): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed> $fieldvals
     */
    public function hMset(string $key, array $fieldvals): \Redis|\RedisCluster|bool;

    /**
     * @return \Redis|\RedisCluster|array<string, mixed>|false
     */
    public function hGetAll(string $key): \Redis|\RedisCluster|array|false;

    public function hDel(string $key, string $field, string ...$other_fields): \Redis|\RedisCluster|int|false;

    public function hExists(string $key, string $field): \Redis|\RedisCluster|bool;

    /**
     * @return \Redis|\RedisCluster|array<string>|false
     */
    public function hKeys(string $key): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function hVals(string $key): \Redis|\RedisCluster|array|false;

    public function hLen(string $key): \Redis|\RedisCluster|int|false;

    public function hStrLen(string $key, string $field): \Redis|\RedisCluster|int|false;

    public function hIncrBy(string $key, string $field, int $value): \Redis|\RedisCluster|int|false;

    public function hIncrByFloat(string $key, string $field, float $value): \Redis|\RedisCluster|float|false;

    public function hSetNx(string $key, string $field, mixed $value): \Redis|\RedisCluster|bool;

    /**
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|string|array<mixed>|false
     */
    public function hRandField(string $key, array|null $options = null): \Redis|\RedisCluster|string|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function hScan(
        string $key,
        int|null &$iterator,
        string|null $pattern = null,
        int $count = 0,
    ): \Redis|\RedisCluster|array|false;

    // =========================================================================
    // List Operations
    // =========================================================================

    public function lPush(string $key, mixed $value, mixed ...$other_values): \Redis|\RedisCluster|int|false;

    public function rPush(string $key, mixed $value, mixed ...$other_values): \Redis|\RedisCluster|int|false;

    public function lPushx(string $key, mixed $value, mixed ...$other_values): \Redis|\RedisCluster|int|false;

    public function rPushx(string $key, mixed $value, mixed ...$other_values): \Redis|\RedisCluster|int|false;

    public function lPop(string $key, int $count = 0): \Redis|\RedisCluster|bool|string|array;

    public function rPop(string $key, int|null $count = null): mixed;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|null|false
     */
    public function blPop(
        string|array $key_or_keys,
        string|float|int $timeout_or_key,
        mixed ...$extra_args,
    ): \Redis|\RedisCluster|array|false|null;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|null|false
     */
    public function brPop(
        string|array $key_or_keys,
        string|float|int $timeout_or_key,
        mixed ...$extra_args,
    ): \Redis|\RedisCluster|array|false|null;

    public function brpoplpush(string $src, string $dst, int|float $timeout): \Redis|\RedisCluster|string|false;

    public function lLen(string $key): \Redis|\RedisCluster|int|false;

    public function lIndex(string $key, int $index): mixed;

    public function lInsert(string $key, string $where, mixed $pivot, mixed $value): \Redis|\RedisCluster|int|false;

    public function lSet(string $key, int $index, mixed $value): \Redis|\RedisCluster|bool;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function lRange(string $key, int $start, int $stop): \Redis|\RedisCluster|array|false;

    public function lTrim(string $key, int $start, int $stop): \Redis|\RedisCluster|bool;

    public function lRem(string $key, mixed $value, int $count = 0): \Redis|\RedisCluster|int|false;

    public function lmove(
        string $src,
        string $dst,
        string $wherefrom,
        string $whereto,
    ): \Redis|\RedisCluster|string|false;

    public function blmove(
        string $src,
        string $dst,
        string $wherefrom,
        string $whereto,
        int|float $timeout,
    ): \Redis|\RedisCluster|string|false;

    /**
     * @param array<string> $keys
     * @return \Redis|\RedisCluster|array<mixed>|null|false
     */
    public function lmpop(array $keys, string $from, int $count = 1): \Redis|\RedisCluster|array|false|null;

    /**
     * @param array<string> $keys
     * @return \Redis|\RedisCluster|array<mixed>|null|false
     */
    public function blmpop(
        float $timeout,
        array $keys,
        string $from,
        int $count = 1,
    ): \Redis|\RedisCluster|array|false|null;

    // =========================================================================
    // Set Operations
    // =========================================================================

    public function sadd(string $key, mixed ...$values): \Redis|\RedisCluster|int|false;

    public function srem(string $key, mixed ...$values): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function smembers(string $key): \Redis|\RedisCluster|array|false;

    public function sismember(string $key, mixed $value): \Redis|\RedisCluster|bool;

    public function scard(string $key): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|string|array<mixed>|false|null
     */
    public function spop(string $key, int|null $count = null): \Redis|\RedisCluster|string|array|false|null;

    /**
     * @return \Redis|\RedisCluster|string|array<mixed>|false
     */
    public function srandmember(string $key, int|null $count = null): \Redis|\RedisCluster|string|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function sinter(mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|array|false;

    public function sinterstore(string $dst, mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function sunion(mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|array|false;

    public function sunionstore(string $dst, mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function sdiff(mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|array|false;

    public function sdiffstore(string $dst, mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|false;

    public function smove(string $src, string $dst, mixed $value): \Redis|\RedisCluster|bool;

    public function sintercard(int $numkeys, mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<bool>|false
     */
    public function smismember(string $key, mixed ...$values): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function sscan(
        string $key,
        int|null &$iterator,
        string|null $pattern = null,
        int $count = 0,
    ): \Redis|\RedisCluster|array|false;

    // =========================================================================
    // Sorted Set Operations
    // =========================================================================

    public function zadd(string $key, mixed $score_or_options, mixed ...$args): \Redis|\RedisCluster|int|float|false;

    public function zrem(string $key, mixed ...$values): \Redis|\RedisCluster|int|false;

    public function zcard(string $key): \Redis|\RedisCluster|int|false;

    public function zcount(string $key, mixed $min, mixed $max): \Redis|\RedisCluster|int|false;

    public function zincrby(string $key, float $value, mixed $member): \Redis|\RedisCluster|float|false;

    /**
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zrange(
        string $key,
        mixed $start,
        mixed $stop,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zrevrange(
        string $key,
        int $start,
        int $stop,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zrangebyscore(
        string $key,
        mixed $min,
        mixed $max,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zrevrangebyscore(
        string $key,
        mixed $max,
        mixed $min,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    public function zrank(string $key, mixed $member): \Redis|\RedisCluster|int|false|null;

    public function zrevrank(string $key, mixed $member): \Redis|\RedisCluster|int|false|null;

    public function zscore(string $key, mixed $member): \Redis|\RedisCluster|float|false;

    /**
     * @return \Redis|\RedisCluster|array<float|false>|false
     */
    public function zmscore(string $key, mixed ...$members): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zpopmax(string $key, int|null $count = null): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zpopmin(string $key, int|null $count = null): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function bzpopmax(
        string|array $key,
        string|int $timeout_or_key,
        mixed ...$extra_args,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function bzpopmin(
        string|array $key,
        string|int $timeout_or_key,
        mixed ...$extra_args,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @param array<float>|null $weights
     * @param array<string, mixed>|null $options
     */
    public function zinterstore(
        string $dst,
        mixed $keys,
        array|null $weights = null,
        array|null $options = null,
    ): \Redis|\RedisCluster|int|false;

    /**
     * @param array<float>|null $weights
     * @param array<string, mixed>|null $options
     */
    public function zunionstore(
        string $dst,
        mixed $keys,
        array|null $weights = null,
        array|null $options = null,
    ): \Redis|\RedisCluster|int|false;

    /**
     * @param array<float>|null $weights
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zinter(
        mixed $keys,
        array|null $weights = null,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @param array<float>|null $weights
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zunion(
        mixed $keys,
        array|null $weights = null,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zdiff(int $numkeys, mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|array|false;

    public function zdiffstore(
        string $dst,
        int $numkeys,
        mixed $key,
        mixed ...$other_keys,
    ): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|string|array<mixed>|false
     */
    public function zrandmember(
        string $key,
        int|null $count = null,
        bool $withscores = false,
    ): \Redis|\RedisCluster|string|array|false;

    /**
     * @param array<string, mixed>|null $options
     */
    public function zrangestore(
        string $dst,
        string $src,
        mixed $min,
        mixed $max,
        array|null $options = null,
    ): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function zscan(
        string $key,
        int|null &$iterator,
        string|null $pattern = null,
        int $count = 0,
    ): \Redis|\RedisCluster|array|false;

    public function zintercard(int $numkeys, mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|int|false;

    public function zlexcount(string $key, string $min, string $max): \Redis|\RedisCluster|int|false;

    public function zremrangebylex(string $key, string $min, string $max): \Redis|\RedisCluster|int|false;

    public function zremrangebyrank(string $key, int $start, int $stop): \Redis|\RedisCluster|int|false;

    public function zremrangebyscore(string $key, mixed $min, mixed $max): \Redis|\RedisCluster|int|false;

    /**
     * @param array<string> $keys
     * @return \Redis|\RedisCluster|array<mixed>|null|false
     */
    public function zmpop(array $keys, string $from, int $count = 1): \Redis|\RedisCluster|array|false|null;

    /**
     * @param array<string> $keys
     * @return \Redis|\RedisCluster|array<mixed>|null|false
     */
    public function bzmpop(
        float $timeout,
        array $keys,
        string $from,
        int $count = 1,
    ): \Redis|\RedisCluster|array|false|null;

    // =========================================================================
    // Geo Operations
    // =========================================================================

    public function geoadd(
        string $key,
        float $lng,
        float $lat,
        string $member,
        mixed ...$other_triples_and_options,
    ): \Redis|\RedisCluster|int|false;

    public function geodist(
        string $key,
        string $src,
        string $dst,
        string|null $unit = null,
    ): \Redis|\RedisCluster|float|false;

    /**
     * @return \Redis|\RedisCluster|array<string>|false
     */
    public function geohash(string $key, string $member, string ...$other_members): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<array{0: float, 1: float}|false>|false
     */
    public function geopos(string $key, string $member, string ...$other_members): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed> $options
     */
    public function georadius(
        string $key,
        float $lng,
        float $lat,
        float $radius,
        string $unit,
        array $options = [],
    ): mixed;

    /**
     * @param array<string, mixed> $options
     */
    public function georadius_ro(
        string $key,
        float $lng,
        float $lat,
        float $radius,
        string $unit,
        array $options = [],
    ): mixed;

    /**
     * @param array<string, mixed> $options
     */
    public function georadiusbymember(
        string $key,
        string $member,
        float $radius,
        string $unit,
        array $options = [],
    ): mixed;

    /**
     * @param array<string, mixed> $options
     */
    public function georadiusbymember_ro(
        string $key,
        string $member,
        float $radius,
        string $unit,
        array $options = [],
    ): mixed;

    /**
     * @param array<string>|string $position
     * @param array<mixed>|int|float $shape
     * @param array<string, mixed> $options
     * @return \Redis|\RedisCluster|array<mixed>
     */
    public function geosearch(
        string $key,
        array|string $position,
        array|int|float $shape,
        string $unit,
        array $options = [],
    ): \Redis|\RedisCluster|array;

    /**
     * @param array<string>|string $position
     * @param array<mixed>|int|float $shape
     * @param array<string, mixed> $options
     * @return \Redis|\RedisCluster|array<mixed>|int|false
     */
    public function geosearchstore(
        string $dst,
        string $src,
        array|string $position,
        array|int|float $shape,
        string $unit,
        array $options = [],
    ): \Redis|\RedisCluster|array|int|false;

    // =========================================================================
    // HyperLogLog Operations
    // =========================================================================

    /**
     * @param array<mixed> $elements
     */
    public function pfadd(string $key, array $elements): \Redis|\RedisCluster|bool;

    public function pfcount(string $key): \Redis|\RedisCluster|int|false;

    /**
     * @param array<string> $keys
     */
    public function pfmerge(string $key, array $keys): \Redis|\RedisCluster|bool;

    // =========================================================================
    // Stream Operations
    // =========================================================================

    /**
     * @param array<string, mixed> $values
     * @param array<string, mixed>|null $options
     */
    public function xadd(
        string $key,
        string $id,
        array $values,
        array|null $options = null,
    ): \Redis|\RedisCluster|string|false;

    public function xlen(string $key): \Redis|\RedisCluster|int|false;

    /**
     * @param array<string, string> $streams
     * @return \Redis|\RedisCluster|array<mixed>|false|null
     */
    public function xread(
        array $streams,
        int|null $count = null,
        int|null $block = null,
    ): \Redis|\RedisCluster|array|false|null;

    /**
     * @param array<string, string> $streams
     * @return \Redis|\RedisCluster|array<mixed>|false|null
     */
    public function xreadgroup(
        string $group,
        string $consumer,
        array $streams,
        int|null $count = null,
        int|null $block = null,
    ): \Redis|\RedisCluster|array|false|null;

    public function xack(string $key, string $group, mixed ...$ids): \Redis|\RedisCluster|int|false;

    /**
     * @param array<string>|string $ids
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function xclaim(
        string $key,
        string $group,
        string $consumer,
        int $min_idle_time,
        mixed $ids,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @param array<string, mixed>|null $options
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function xautoclaim(
        string $key,
        string $group,
        string $consumer,
        int $min_idle_time,
        string $start,
        array|null $options = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function xpending(
        string $key,
        string $group,
        string|null $start = null,
        string|null $end = null,
        int|null $count = null,
        string|null $consumer = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function xrange(
        string $key,
        mixed $start,
        mixed $end,
        int|null $count = null,
    ): \Redis|\RedisCluster|array|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function xrevrange(
        string $key,
        mixed $end,
        mixed $start,
        int|null $count = null,
    ): \Redis|\RedisCluster|array|false;

    public function xdel(string $key, mixed ...$ids): \Redis|\RedisCluster|int|false;

    public function xtrim(
        string $key,
        string $strategy,
        int $count,
        bool|null $approximation = null,
    ): \Redis|\RedisCluster|int|false;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false|string|bool
     */
    public function xgroup(
        string $operation,
        string $key,
        string $group,
        string $id_or_start,
        bool|null $mkstream = null,
    ): mixed;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false|string
     */
    public function xinfo(
        string $operation,
        string $key,
        string|null $group = null,
    ): \Redis|\RedisCluster|array|false|string;

    // =========================================================================
    // Transaction Operations
    // =========================================================================

    public function multi(int $mode = \Redis::MULTI): \Redis|\RedisCluster|bool;

    /**
     * @return \Redis|\RedisCluster|array<mixed>|false
     */
    public function exec(): \Redis|\RedisCluster|array|false;

    public function discard(): \Redis|\RedisCluster|bool;

    public function watch(mixed $key, mixed ...$other_keys): \Redis|\RedisCluster|bool;

    public function unwatch(): \Redis|\RedisCluster|bool;

    // =========================================================================
    // Scripting Operations
    // =========================================================================

    /**
     * @param array<mixed> $args
     */
    public function eval(string $script, array $args = [], int $num_keys = 0): mixed;

    /**
     * @param array<mixed> $args
     */
    public function eval_ro(string $script, array $args = [], int $num_keys = 0): mixed;

    /**
     * @param array<mixed> $args
     */
    public function evalsha(string $sha1, array $args = [], int $num_keys = 0): mixed;

    /**
     * @param array<mixed> $args
     */
    public function evalsha_ro(string $sha1, array $args = [], int $num_keys = 0): mixed;

    public function script(string $operation, mixed ...$args): \Redis|\RedisCluster|bool|string|array;

    // =========================================================================
    // Server Operations
    // =========================================================================

    public function dbSize(): \Redis|\RedisCluster|int|false;

    public function flushDb(bool|null $sync = null): \Redis|\RedisCluster|bool;

    public function flushAll(bool|null $sync = null): \Redis|\RedisCluster|bool;

    public function info(string|null $section = null): \Redis|\RedisCluster|array|string|false;

    /**
     * @return \Redis|\RedisCluster|array{0: string, 1: string}|false
     */
    public function time(): \Redis|\RedisCluster|array|false;

    public function lastSave(): \Redis|\RedisCluster|int|false;

    public function bgSave(): \Redis|\RedisCluster|bool;

    public function bgrewriteaof(): \Redis|\RedisCluster|bool;

    public function save(): \Redis|\RedisCluster|bool;

    // =========================================================================
    // Pub/Sub Operations
    // =========================================================================

    public function publish(string $channel, string $message): \Redis|\RedisCluster|int|false;

    // =========================================================================
    // Utility Methods
    // =========================================================================

    public function _compress(string $value): string;

    public function _uncompress(string $value): string;

    public function _prefix(string $key): string;

    public function _serialize(mixed $value): string;

    public function _unserialize(string $value): mixed;

    public function _pack(mixed $value): string;

    public function _unpack(string $value): mixed;
}
