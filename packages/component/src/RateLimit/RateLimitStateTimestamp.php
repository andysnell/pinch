<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\RateLimit;

use PhoneBurner\Pinch\Math\Interval\NumericRange;
use Random\IntervalBoundary;
use Traversable;

/**
 * @implements NumericRange<int>
 * @implements \IteratorAggregate<int, int>
 */
final class RateLimitStateTimestamp implements NumericRange, \IteratorAggregate, \Countable
{
    public private(set) IntervalBoundary $boundary = IntervalBoundary::ClosedOpen;

    public private(set) int $start;

    public private(set) int $reset;

    /**
     * @param int<1,max> $seconds
     */
    public function __construct(int $timestamp, private int $seconds = 1)
    {
        $this->start = $seconds === 1 ? $timestamp : \intdiv($timestamp, $seconds) * $seconds;
        $this->reset = $this->start + $this->seconds;
    }

    public function getIterator(): Traversable
    {
        for ($i = $this->start; $i < $this->reset; ++$i) {
            yield $i;
        }
    }

    public function count(): int
    {
        return $this->seconds;
    }

    public function contains(float|int $value): bool
    {
        return $value >= $this->start && $value < $this->reset;
    }

    public function unbounded(): bool
    {
        return false;
    }

    public function min(): int
    {
        return $this->start;
    }

    public function max(): int
    {
        return $this->reset;
    }
}
