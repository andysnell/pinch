<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Timer;

use PhoneBurner\Pinch\Time\Clock\HighResolutionTimer;
use PhoneBurner\Pinch\Time\Clock\SystemHighResolutionTimer;
use PhoneBurner\Pinch\Time\Timer\ElapsedTime;

final class StopWatch
{
    public private(set) int $start;

    public function __construct(
        private readonly HighResolutionTimer $timer = new SystemHighResolutionTimer(),
    ) {
        $this->start = $timer->now();
    }

    public static function start(): self
    {
        return new self();
    }

    public function elapsed(): ElapsedTime
    {
        return new ElapsedTime($this->timer->now() - $this->start);
    }
}
