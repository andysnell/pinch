<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Console\CircuitBreaker\Exception;

use PhoneBurner\Pinch\Framework\Console\CircuitBreaker\InfiniteLoopCircuitBreaker;

final class InfiniteLoopCircuitBreakerTripped extends \LogicException
{
    public function __construct(InfiniteLoopCircuitBreaker $circuit_breaker)
    {
        parent::__construct(
            \sprintf("possible infinite loop detected (threshold: %s iterations)", $circuit_breaker->counter),
        );
    }
}
