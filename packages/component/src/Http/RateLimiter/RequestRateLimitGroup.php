<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\IpAddress\IpAddress;
use Ramsey\Uuid\UuidInterface;

final readonly class RequestRateLimitGroup
{
    public function __construct(
        public string $name,
        public \Stringable|\BackedEnum|string|int $id,
    ) {
    }

    public static function fromIpAddress(IpAddress $ip): self
    {
        return new self('ip', $ip);
    }

    public static function fromUuid(UuidInterface|string $uuid): self
    {
        return new self('uuid', $uuid);
    }

    public function key(): CacheKey
    {
        return new CacheKey($this->name, $this->id);
    }
}
