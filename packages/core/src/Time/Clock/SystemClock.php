<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Time\TimeUnit;

use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_MICROSECOND;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_MILLISECOND;
use const PhoneBurner\Pinch\Time\NANOSECONDS_IN_SECOND;

class SystemClock implements Clock
{
    #[\Override]
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    /**
     * @return positive-int
     */
    #[\Override]
    public function timestamp(): int
    {
        return \time();
    }

    #[\Override]
    public function microtime(): float
    {
        return \microtime(true);
    }

    #[\Override]
    public function sleep(int $delay, TimeUnit $unit = TimeUnit::Microsecond): bool
    {
        $delay >= 0 || throw new \UnexpectedValueException('Delay must be greater than or equal to zero.');
        $nanoseconds = match ($unit) {
            TimeUnit::Second => $delay * NANOSECONDS_IN_SECOND,
            TimeUnit::Millisecond => $delay * NANOSECONDS_IN_MILLISECOND,
            TimeUnit::Microsecond => $delay * NANOSECONDS_IN_MICROSECOND,
            TimeUnit::Nanosecond => $delay,
            default => throw new \UnexpectedValueException('Unsupported time unit for sleep(): ' . $unit->name),
        };
        $seconds = \intdiv($nanoseconds, NANOSECONDS_IN_SECOND);
        $nanoseconds %= NANOSECONDS_IN_SECOND;

        return \time_nanosleep($seconds, $nanoseconds) === true;
    }
}
