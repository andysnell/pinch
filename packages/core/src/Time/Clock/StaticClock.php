<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Time\Domain\TimeUnit;

use function PhoneBurner\Pinch\Time\parse_carbon;

final readonly class StaticClock implements Clock
{
    private CarbonImmutable $now;

    public function __construct(\DateTimeInterface|string|int|float $now = new CarbonImmutable())
    {
        $this->now = parse_carbon($now) ?? new CarbonImmutable();
    }

    #[\Override]
    public function now(): CarbonImmutable
    {
        return $this->now;
    }

    public function timestamp(): int
    {
        return $this->now->getTimestamp();
    }

    public function microtime(): float
    {
        return (float)$this->now->format('U.u');
    }

    public function sleep(int $delay, TimeUnit $unit = TimeUnit::Microsecond): true
    {
        return true;
    }
}
