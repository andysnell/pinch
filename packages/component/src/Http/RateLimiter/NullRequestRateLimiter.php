<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\Event\RequestRateLimitUpdated;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PhoneBurner\Pinch\Time\Clock\Clock;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class NullRequestRateLimiter implements RequestRateLimiter
{
    public function __construct(
        private Clock $clock,
        private EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function throttle(RequestRateLimitGroup $group, RequestRateLimits $limits): RequestRateLimitResult
    {
        $result = DefaultRequestRateLimitResult::allowed(
            remaining_per_second: $limits->second,
            remaining_per_minute: $limits->minute,
            reset_time: $this->clock->now()->addMinutes(1),
            rate_limits: $limits,
        );

        $this->event_dispatcher->dispatch(new RequestRateLimitUpdated($result));

        return $result;
    }
}
