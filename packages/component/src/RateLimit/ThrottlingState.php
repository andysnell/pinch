<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use const PhoneBurner\Pinch\Time\SECONDS_IN_DAY;
use const PhoneBurner\Pinch\Time\SECONDS_IN_HOUR;
use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

final class ThrottlingState
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

    // phpcs:disable
    public \DateTimeImmutable $hour_floor {
        get => \DateTimeImmutable::createFromTimestamp(
            \intdiv($this->timestamp->getTimestamp(), SECONDS_IN_HOUR) * SECONDS_IN_HOUR,
        );
    }
    // phpcs:enable

    // phpcs:disable
    public \DateTimeImmutable $hour_reset {
        get => \DateTimeImmutable::createFromTimestamp(
            \intdiv($this->timestamp->getTimestamp(), SECONDS_IN_HOUR) * SECONDS_IN_HOUR + SECONDS_IN_HOUR,
        );
    }
    // phpcs:enable

    // phpcs:disable
    public \DateTimeImmutable $day_floor {
        get => \DateTimeImmutable::createFromTimestamp(
            \intdiv($this->timestamp->getTimestamp(), SECONDS_IN_DAY) * SECONDS_IN_DAY,
        );
    }
    // phpcs:enable

    // phpcs:disable
    public \DateTimeImmutable $day_reset {
        get => \DateTimeImmutable::createFromTimestamp(
            \intdiv($this->timestamp->getTimestamp(), SECONDS_IN_DAY) * SECONDS_IN_DAY + SECONDS_IN_DAY,
        );
    }
    // phpcs:enable

    public function __construct(
        public \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
        public int $second = 0,
        public int $minute = 0,
        public int $hour = 0,
        public int $day = 0,
    ) {
    }
}
