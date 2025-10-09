<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Console\CircuitBreaker;

use PhoneBurner\Pinch\Framework\Console\CircuitBreaker\Exception\InfiniteLoopCircuitBreakerTripped;

final class InfiniteLoopCircuitBreaker
{
    public private(set) int $counter = 0;

    public private(set) bool $tripped = false;

    public function __construct(
        public readonly int $threshold = 250,
    ) {
    }

    public function increment(): int
    {
        // if we have already tripped this circuit breaker, but are attempting to increment again
        // something has gone really, terribly wrong. Consequences be damned, exit the process.
        if ($this->tripped) {
            /** @phpstan-ignore-next-line */
            exit('unrecoverable infinite loop detected, terminating process');
        }

        if ($this->counter <= $this->threshold) {
            return ++$this->counter;
        }

        $this->tripped = true;
        throw new InfiniteLoopCircuitBreakerTripped($this);
    }

    public function reset(): int
    {
        return $this->counter = 0;
    }
}
