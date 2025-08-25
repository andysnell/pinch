<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use const PhoneBurner\Pinch\Time\SECONDS_IN_DAY;
use const PhoneBurner\Pinch\Time\SECONDS_IN_HOUR;
use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

final readonly class RateLimitStateTimestamps
{
    public RateLimitStateTimestamp $second;

    public RateLimitStateTimestamp $minute;

    public RateLimitStateTimestamp $hour;

    public RateLimitStateTimestamp $day;

    public function __construct(public \DateTimeImmutable $datetime)
    {
        $timestamp = $this->datetime->getTimestamp();
        $this->second = new RateLimitStateTimestamp($timestamp);
        $this->minute = new RateLimitStateTimestamp($timestamp, SECONDS_IN_MINUTE);
        $this->hour = new RateLimitStateTimestamp($timestamp, SECONDS_IN_HOUR);
        $this->day = new RateLimitStateTimestamp($timestamp, SECONDS_IN_DAY);
    }
}
