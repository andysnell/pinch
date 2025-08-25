<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

final class RequestRateLimitState
{
    // phpcs:disable
    public \DateTimeImmutable $second_floor {
        get => $this->timestamp;
    }
    // phpcs:enable

    // phpcs:disable
    public \DateTimeImmutable $second_reset {
        get => \DateTimeImmutable::createFromTimestamp(
            $this->timestamp->getTimestamp() + 1
        );
    }
    // phpcs:enable

    // phpcs:disable
    public \DateTimeImmutable $minute_floor {
        get => \DateTimeImmutable::createFromTimestamp(
            \intdiv($this->timestamp->getTimestamp(), SECONDS_IN_MINUTE)  * SECONDS_IN_MINUTE,
        );
    }
    // phpcs:enable

    // phpcs:disable
    public \DateTimeImmutable $minute_reset {
        get => \DateTimeImmutable::createFromTimestamp(
            \intdiv($this->timestamp->getTimestamp(), SECONDS_IN_MINUTE)  * SECONDS_IN_MINUTE + SECONDS_IN_MINUTE,
        );
    }
    // phpcs:enable

    public function __construct(
        public \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
        public int $second = 0,
        public int $minute = 0,
    ) {
    }
}
