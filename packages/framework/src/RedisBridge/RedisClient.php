<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\RedisBridge;

use function PhoneBurner\Pinch\nullify;

class RedisClient
{
    public function __construct(public readonly \Redis $wrapped)
    {
    }

    // Connection methods
    public function connect(
        string $host,
        int $port = 6379,
        float $timeout = 0,
        mixed $reserved = null,
        int $retry_interval = 0,
        float $read_timeout = 0,
    ): bool {
        return $this->wrapped->connect($host, $port, $timeout, $reserved, $retry_interval, $read_timeout);
    }

    public function pconnect(
        string $host,
        int $port = 6379,
        float $timeout = 0,
        string $persistent_id = '',
        int $retry_interval = 0,
        float $read_timeout = 0,
    ): bool {
        return $this->wrapped->pconnect($host, $port, $timeout, $persistent_id, $retry_interval, $read_timeout);
    }

    public function close(): bool
    {
        return $this->wrapped->close();
    }

    public function isConnected(): bool
    {
        return $this->wrapped->isConnected();
    }

    public function auth(mixed $auth): bool
    {
        return $this->wrapped->auth($auth);
    }

    public function select(int $db): bool
    {
        return $this->wrapped->select($db);
    }

    public function swapdb(int $db1, int $db2): bool
    {
        return $this->wrapped->swapdb($db1, $db2);
    }

    // Basic key operations
    public function get(string $key): string|null
    {
        return nullify($this->wrapped->get($key));
    }

    public function set(string $key, mixed $value, mixed $options = null): bool|\Redis
    {
        return $this->wrapped->set($key, $value, $options);
    }

    public function del(string|array $key, string ...$other_keys): int
    {
        return $this->wrapped->del($key, ...$other_keys);
    }

    public function exists(mixed $key, mixed ...$other_keys): int|bool
    {
        return $this->wrapped->exists($key, ...$other_keys);
    }

    public function expire(string $key, int $timeout): bool
    {
        return $this->wrapped->expire($key, $timeout);
    }

    public function expireAt(string $key, int $timestamp): bool
    {
        return $this->wrapped->expireAt($key, $timestamp);
    }

    public function pexpire(string $key, int $timeout): bool
    {
        return $this->wrapped->pexpire($key, $timeout);
    }

    public function pexpireAt(string $key, int $timestamp): bool
    {
        return $this->wrapped->pexpireAt($key, $timestamp);
    }

    public function ttl(string $key): int
    {
        return $this->wrapped->ttl($key);
    }

    public function pttl(string $key): int
    {
        return $this->wrapped->pttl($key);
    }

    public function persist(string $key): bool
    {
        return $this->wrapped->persist($key);
    }

    public function keys(string $pattern): array
    {
        return $this->wrapped->keys($pattern);
    }

    public function scan(
        int &$iterator,
        string|null $pattern = null,
        int $count = 0,
        string|null $type = null,
    ): array|false {
        return $this->wrapped->scan($iterator, $pattern, $count, $type);
    }

    public function type(string $key): int
    {
        return $this->wrapped->type($key);
    }

    public function rename(string $key, string $newkey): bool
    {
        return $this->wrapped->rename($key, $newkey);
    }

    public function renameNx(string $key, string $newkey): bool
    {
        return $this->wrapped->renamenx($key, $newkey);
    }

    // String operations
    public function append(string $key, string $value): int
    {
        return $this->wrapped->append($key, $value);
    }

    public function getSet(string $key, string $value): string|false
    {
        return $this->wrapped->getSet($key, $value);
    }

    public function strlen(string $key): int
    {
        return $this->wrapped->strlen($key);
    }

    public function substr(string $key, int $start, int $end): string|false
    {
        return $this->wrapped->substr($key, $start, $end);
    }

    public function getRange(string $key, int $start, int $end): string
    {
        return $this->wrapped->getRange($key, $start, $end);
    }

    public function setRange(string $key, int $offset, string $value): int
    {
        return $this->wrapped->setRange($key, $offset, $value);
    }

    public function getBit(string $key, int $offset): int
    {
        return $this->wrapped->getBit($key, $offset);
    }

    public function setBit(string $key, int $offset, int $value): int
    {
        return $this->wrapped->setBit($key, $offset, $value);
    }

    public function bitCount(string $key, int $start = 0, int $end = -1): int
    {
        return $this->wrapped->bitCount($key, $start, $end);
    }

    public function bitOp(string $operation, string $ret_key, string $key, string ...$other_keys): int
    {
        return $this->wrapped->bitOp($operation, $ret_key, $key, ...$other_keys);
    }

    // Increment/Decrement operations
    public function incr(string $key): int
    {
        return $this->wrapped->incr($key);
    }

    public function incrBy(string $key, int $value): int
    {
        return $this->wrapped->incrBy($key, $value);
    }

    public function incrByFloat(string $key, float $increment): float
    {
        return $this->wrapped->incrByFloat($key, $increment);
    }

    public function decr(string $key): int
    {
        return $this->wrapped->decr($key);
    }

    public function decrBy(string $key, int $value): int
    {
        return $this->wrapped->decrBy($key, $value);
    }

    // Multiple key operations
    public function mGet(array $keys): array
    {
        return $this->wrapped->mGet($keys);
    }

    public function mSet(array $pairs): bool
    {
        return $this->wrapped->mSet($pairs);
    }

    public function mSetNx(array $pairs): bool
    {
        return $this->wrapped->mSetNx($pairs);
    }

    // List operations
    public function lPush(string $key, mixed ...$values): int|false
    {
        return $this->wrapped->lPush($key, ...$values);
    }

    public function rPush(string $key, mixed ...$values): int|false
    {
        return $this->wrapped->rPush($key, ...$values);
    }

    public function lPushx(string $key, mixed $value): int|false
    {
        return $this->wrapped->lPushx($key, $value);
    }

    public function rPushx(string $key, mixed $value): int|false
    {
        return $this->wrapped->rPushx($key, $value);
    }

    public function lPop(string $key): mixed
    {
        return $this->wrapped->lPop($key);
    }

    public function rPop(string $key): mixed
    {
        return $this->wrapped->rPop($key);
    }

    public function blPop(string|array $key, string|int $timeout_or_key, mixed ...$extra_args): array|false
    {
        return $this->wrapped->blPop($key, $timeout_or_key, ...$extra_args);
    }

    public function brPop(string|array $key, string|int $timeout_or_key, mixed ...$extra_args): array|false
    {
        return $this->wrapped->brPop($key, $timeout_or_key, ...$extra_args);
    }

    public function lLen(string $key): int
    {
        return $this->wrapped->lLen($key);
    }

    public function lIndex(string $key, int $index): mixed
    {
        return $this->wrapped->lIndex($key, $index);
    }

    public function lSet(string $key, int $index, mixed $value): bool
    {
        return $this->wrapped->lSet($key, $index, $value);
    }

    public function lRange(string $key, int $start, int $end): array
    {
        return $this->wrapped->lRange($key, $start, $end);
    }

    public function lTrim(string $key, int $start, int $stop): bool
    {
        return $this->wrapped->lTrim($key, $start, $stop);
    }

    public function lRem(string $key, mixed $value, int $count): int|false
    {
        return $this->wrapped->lRem($key, $value, $count);
    }

    public function lInsert(string $key, int $position, mixed $pivot, mixed $value): int
    {
        return $this->wrapped->lInsert($key, $position, $pivot, $value);
    }

    public function rPopLPush(string $src, string $dst): mixed
    {
        return $this->wrapped->rPopLPush($src, $dst);
    }

    public function brPopLPush(string $src, string $dst, int $timeout): mixed
    {
        return $this->wrapped->brPopLPush($src, $dst, $timeout);
    }

    // Set operations
    public function sAdd(string $key, mixed ...$values): int|false
    {
        return $this->wrapped->sAdd($key, ...$values);
    }

    public function sRem(string $key, mixed ...$members): int
    {
        return $this->wrapped->sRem($key, ...$members);
    }

    public function sMove(string $src, string $dst, mixed $member): bool
    {
        return $this->wrapped->sMove($src, $dst, $member);
    }

    public function sIsMember(string $key, mixed $value): bool
    {
        return $this->wrapped->sIsMember($key, $value);
    }

    public function sCard(string $key): int
    {
        return $this->wrapped->sCard($key);
    }

    public function sPop(string $key, int $count = 1): mixed
    {
        return $this->wrapped->sPop($key, $count);
    }

    public function sRandMember(string $key, int $count = 1): mixed
    {
        return $this->wrapped->sRandMember($key, $count);
    }

    public function sInter(string|array $key, string ...$other_keys): array|false
    {
        return $this->wrapped->sInter($key, ...$other_keys);
    }

    public function sInterStore(string $dst, string|array $key, string ...$other_keys): int|false
    {
        return $this->wrapped->sInterStore($dst, $key, ...$other_keys);
    }

    public function sUnion(string|array $key, string ...$other_keys): array|false
    {
        return $this->wrapped->sUnion($key, ...$other_keys);
    }

    public function sUnionStore(string $dst, string|array $key, string ...$other_keys): int|false
    {
        return $this->wrapped->sUnionStore($dst, $key, ...$other_keys);
    }

    public function sDiff(string|array $key, string ...$other_keys): array|false
    {
        return $this->wrapped->sDiff($key, ...$other_keys);
    }

    public function sDiffStore(string $dst, string|array $key, string ...$other_keys): int|false
    {
        return $this->wrapped->sDiffStore($dst, $key, ...$other_keys);
    }

    public function sMembers(string $key): array
    {
        return $this->wrapped->sMembers($key);
    }

    public function sScan(string $key, int &$iterator, string|null $pattern = null, int $count = 0): array|false
    {
        return $this->wrapped->sScan($key, $iterator, $pattern, $count);
    }

    // Hash operations
    public function hSet(string $key, mixed $hashKey, mixed $value): int|false
    {
        return $this->wrapped->hSet($key, $hashKey, $value);
    }

    public function hSetNx(string $key, string $hashKey, mixed $value): bool
    {
        return $this->wrapped->hSetNx($key, $hashKey, $value);
    }

    public function hGet(string $key, string $hashKey): mixed
    {
        return nullify($this->wrapped->hGet($key, $hashKey));
    }

    public function hLen(string $key): int|false
    {
        return $this->wrapped->hLen($key);
    }

    public function hDel(string $key, string $hashKey, string ...$other_hashkeys): int|false
    {
        return $this->wrapped->hDel($key, $hashKey, ...$other_hashkeys);
    }

    public function hKeys(string $key): array
    {
        return $this->wrapped->hKeys($key);
    }

    public function hVals(string $key): array
    {
        return $this->wrapped->hVals($key);
    }

    public function hGetAll(string $key): array
    {
        return $this->wrapped->hGetAll($key);
    }

    public function hExists(string $key, string $hashKey): bool
    {
        return $this->wrapped->hExists($key, $hashKey);
    }

    public function hIncrBy(string $key, string $hashKey, int $value): int
    {
        return $this->wrapped->hIncrBy($key, $hashKey, $value);
    }

    public function hIncrByFloat(string $key, string $hashKey, float $value): float
    {
        return $this->wrapped->hIncrByFloat($key, $hashKey, $value);
    }

    public function hMSet(string $key, array $pairs): bool
    {
        return $this->wrapped->hMSet($key, $pairs);
    }

    public function hMGet(string $key, array $keys): array
    {
        return $this->wrapped->hMGet($key, $keys);
    }

    public function hScan(string $key, int &$iterator, string|null $pattern = null, int $count = 0): array
    {
        return $this->wrapped->hScan($key, $iterator, $pattern, $count);
    }

    public function hStrLen(string $key, string $field): int
    {
        return $this->wrapped->hStrLen($key, $field);
    }

    // Sorted set operations
    public function zAdd(string $key, array|float $score_or_options, mixed ...$more_scores_and_mems): int|false
    {
        return $this->wrapped->zAdd($key, $score_or_options, ...$more_scores_and_mems);
    }

    public function zRem(string $key, mixed $member, mixed ...$other_members): int
    {
        return $this->wrapped->zRem($key, $member, ...$other_members);
    }

    public function zRange(string $key, int $start, int $end, bool|array|null $options = null): array
    {
        return $this->wrapped->zRange($key, $start, $end, $options);
    }

    public function zRevRange(string $key, int $start, int $end, bool $withscores = false): array
    {
        return $this->wrapped->zRevRange($key, $start, $end, $withscores);
    }

    public function zRangeByScore(string $key, string $start, string $end, array $options = []): array
    {
        return $this->wrapped->zRangeByScore($key, $start, $end, $options);
    }

    public function zRevRangeByScore(string $key, string $start, string $end, array $options = []): array
    {
        return $this->wrapped->zRevRangeByScore($key, $start, $end, $options);
    }

    public function zCard(string $key): int
    {
        return $this->wrapped->zCard($key);
    }

    public function zScore(string $key, mixed $member): float|false
    {
        return $this->wrapped->zScore($key, $member);
    }

    public function zRank(string $key, mixed $member): int|false
    {
        return $this->wrapped->zRank($key, $member);
    }

    public function zRevRank(string $key, mixed $member): int|false
    {
        return $this->wrapped->zRevRank($key, $member);
    }

    public function zIncrBy(string $key, float $value, mixed $member): float
    {
        return $this->wrapped->zIncrBy($key, $value, $member);
    }

    public function zUnionStore(
        string $output,
        array $zsetkeys,
        array|null $weights = null,
        string|null $aggregateFunction = null,
    ): int {
        return $this->wrapped->zUnionStore($output, $zsetkeys, $weights, $aggregateFunction);
    }

    public function zInterStore(
        string $output,
        array $zsetkeys,
        array|null $weights = null,
        string|null $aggregateFunction = null,
    ): int {
        return $this->wrapped->zInterStore($output, $zsetkeys, $weights, $aggregateFunction);
    }

    public function zCount(string $key, string $start, string $end): int
    {
        return $this->wrapped->zCount($key, $start, $end);
    }

    public function zRemRangeByScore(string $key, string $start, string $end): int
    {
        return $this->wrapped->zRemRangeByScore($key, $start, $end);
    }

    public function zRemRangeByRank(string $key, int $start, int $end): int
    {
        return $this->wrapped->zRemRangeByRank($key, $start, $end);
    }

    public function zScan(string $key, int &$iterator, string|null $pattern = null, int $count = 0): array|false
    {
        return $this->wrapped->zScan($key, $iterator, $pattern, $count);
    }

    // Transaction methods
    public function multi(int $mode = \Redis::MULTI): \Redis|bool
    {
        return $this->wrapped->multi($mode);
    }

    public function exec(): array|false
    {
        return $this->wrapped->exec();
    }

    public function discard(): bool
    {
        return $this->wrapped->discard();
    }

    public function watch(string $key, string ...$other_keys): bool
    {
        return $this->wrapped->watch($key, ...$other_keys);
    }

    public function unwatch(): bool
    {
        return $this->wrapped->unwatch();
    }

    public function pipeline(): \Redis|bool
    {
        return $this->wrapped->pipeline();
    }

    // Pub/Sub methods
    public function publish(string $channel, string $message): int|false
    {
        return $this->wrapped->publish($channel, $message);
    }

    public function subscribe(array $channels, callable $callback): mixed
    {
        return $this->wrapped->subscribe($channels, $callback);
    }

    public function psubscribe(array $patterns, callable $callback): mixed
    {
        return $this->wrapped->psubscribe($patterns, $callback);
    }

    public function unsubscribe(array $channels = []): array|bool
    {
        return $this->wrapped->unsubscribe($channels);
    }

    public function punsubscribe(array $patterns = []): array|bool
    {
        return $this->wrapped->punsubscribe($patterns);
    }

    public function pubSubChannels(string $pattern = '*'): array
    {
        return $this->wrapped->pubSubChannels($pattern);
    }

    public function pubSubNumSub(array $channels): array
    {
        return $this->wrapped->pubSubNumSub($channels);
    }

    public function pubSubNumPat(): int
    {
        return $this->wrapped->pubSubNumPat();
    }

    // Server info methods
    public function info(string|null $option = null): array|string
    {
        return $this->wrapped->info($option);
    }

    public function dbSize(): int
    {
        return $this->wrapped->dbSize();
    }

    public function flushDB(bool $async = false): bool
    {
        return $this->wrapped->flushDB($async);
    }

    public function flushAll(bool $async = false): bool
    {
        return $this->wrapped->flushAll($async);
    }

    public function save(): bool
    {
        return $this->wrapped->save();
    }

    public function bgSave(): bool
    {
        return $this->wrapped->bgSave();
    }

    public function bgrewriteaof(): bool
    {
        return $this->wrapped->bgrewriteaof();
    }

    public function lastSave(): int
    {
        return $this->wrapped->lastSave();
    }

    public function ping(string|null $message = null): mixed
    {
        return $this->wrapped->ping($message);
    }

    public function echo(string $message): string
    {
        return $this->wrapped->echo($message);
    }

    // Configuration methods
    public function config(string $operation, string $key, mixed $value = null): mixed
    {
        return $this->wrapped->config($operation, $key, $value);
    }

    public function setOption(int $option, mixed $value): bool
    {
        return $this->wrapped->setOption($option, $value);
    }

    public function getOption(int $option): mixed
    {
        return $this->wrapped->getOption($option);
    }

    public function slowlog(string $operation, int|null $length = null): mixed
    {
        return $this->wrapped->slowlog($operation, $length);
    }

    // Script methods
    public function eval(string $script, array $args = [], int $numkeys = 0): mixed
    {
        return $this->wrapped->eval($script, $args, $numkeys);
    }

    public function evalSha(string $script_sha, array $args = [], int $numkeys = 0): mixed
    {
        return $this->wrapped->evalSha($script_sha, $args, $numkeys);
    }

    public function script(string $command, string ...$scripts): mixed
    {
        return $this->wrapped->script($command, ...$scripts);
    }

    // Utility methods
    public function dump(string $key): string|false
    {
        return $this->wrapped->dump($key);
    }

    public function restore(string $key, int $ttl, string $value, array|null $options = null): bool
    {
        return $this->wrapped->restore($key, $ttl, $value, $options);
    }

    public function migrate(
        string $host,
        int $port,
        string|array $key,
        int $db,
        int $timeout,
        bool $copy = false,
        bool $replace = false,
    ): bool {
        return $this->wrapped->migrate($host, $port, $key, $db, $timeout, $copy, $replace);
    }

    public function time(): array
    {
        return $this->wrapped->time();
    }

    public function randomKey(): string|false
    {
        return $this->wrapped->randomKey();
    }

    // Geospatial methods
    public function geoAdd(
        string $key,
        float $longitude,
        float $latitude,
        string $member,
        mixed ...$other_triples_and_options,
    ): int {
        return $this->wrapped->geoAdd($key, $longitude, $latitude, $member, ...$other_triples_and_options);
    }

    public function geoDist(string $key, string $member1, string $member2, string $unit = 'm'): float|false
    {
        return $this->wrapped->geoDist($key, $member1, $member2, $unit);
    }

    public function geoHash(string $key, string $member, string ...$other_members): array
    {
        return $this->wrapped->geoHash($key, $member, ...$other_members);
    }

    public function geoPos(string $key, string $member, string ...$other_members): array
    {
        return $this->wrapped->geoPos($key, $member, ...$other_members);
    }

    public function geoRadius(
        string $key,
        float $longitude,
        float $latitude,
        float $radius,
        string $unit,
        array $options = [],
    ): mixed {
        return $this->wrapped->geoRadius($key, $longitude, $latitude, $radius, $unit, $options);
    }

    public function geoRadiusByMember(
        string $key,
        string $member,
        float $radius,
        string $unit,
        array $options = [],
    ): mixed {
        return $this->wrapped->geoRadiusByMember($key, $member, $radius, $unit, $options);
    }

    // Additional methods from newer Redis versions
    public function xAdd(
        string $str_key,
        string $str_id,
        array $arr_message,
        int $i_maxlen = 0,
        bool $boo_approximate = false,
    ): string|false {
        return $this->wrapped->xAdd($str_key, $str_id, $arr_message, $i_maxlen, $boo_approximate);
    }

    public function xRead(array $arr_streams, int $i_count = -1, int $i_block = -1): array|bool|null
    {
        return $this->wrapped->xRead($arr_streams, $i_count, $i_block);
    }

    public function client(string $command, mixed $arg = null): mixed
    {
        return $this->wrapped->client($command, $arg);
    }

    public function getAuth(): mixed
    {
        return $this->wrapped->getAuth();
    }

    public function getDBNum(): int
    {
        return $this->wrapped->getDBNum();
    }

    public function getHost(): string
    {
        return $this->wrapped->getHost();
    }

    public function getPort(): int
    {
        return $this->wrapped->getPort();
    }

    public function getTimeout(): float
    {
        return $this->wrapped->getTimeout();
    }

    public function getReadTimeout(): float
    {
        return $this->wrapped->getReadTimeout();
    }

    public function getPersistentID(): string|null
    {
        return $this->wrapped->getPersistentID();
    }

    public function getLastError(): string|null
    {
        return $this->wrapped->getLastError();
    }

    public function clearLastError(): bool
    {
        return $this->wrapped->clearLastError();
    }
}
