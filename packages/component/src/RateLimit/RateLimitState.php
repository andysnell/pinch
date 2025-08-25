<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

final class RateLimitState
{
    public function __construct(
        public RateLimitStateTimestamps $timestamps,
        public int $second = 0,
        public int $minute = 0,
        public int $hour = 0,
        public int $day = 0,
    ) {
    }
}
