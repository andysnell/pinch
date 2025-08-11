<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

use Random\IntervalBoundary;

/**
 * @implements NumericRange<int>
 * @implements \IteratorAggregate<int, int>
 */
final class IntegerRange implements NumericRange, \IteratorAggregate, \Countable
{
    /**
     * @param IntervalBoundary $boundary default includes both endpoints in interval
     */
    public function __construct(
        public int $min,
        public int $max,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        if ($this->max < $this->min) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): int
    {
        return $this->min;
    }

    public function max(): int
    {
        return $this->max;
    }

    public function contains(int|float $value): bool
    {
        return match ($this->boundary) {
            IntervalBoundary::ClosedClosed => $value >= $this->min && $value <= $this->max,
            IntervalBoundary::OpenOpen => $value > $this->min && $value < $this->max,
            IntervalBoundary::OpenClosed => $value > $this->min && $value <= $this->max,
            IntervalBoundary::ClosedOpen => $value >= $this->min && $value < $this->max,
        };
    }

    public function getIterator(): \Generator
    {
        $i = $this->min;
        if ($this->includesMinimum()) {
            yield $i;
        }
        while (++$i < $this->max) {
            yield $i;
        }
        if ($i === $this->max && $this->includesMaximum()) {
            yield $i;
        }
    }

    public function includesMinimum(): bool
    {
        return $this->boundary === IntervalBoundary::ClosedClosed || $this->boundary === IntervalBoundary::ClosedOpen;
    }

    public function includesMaximum(): bool
    {
        return $this->boundary === IntervalBoundary::ClosedClosed || $this->boundary === IntervalBoundary::OpenClosed;
    }

    public function unbounded(): false
    {
        return false;
    }

    public function count(): int
    {
        if ($this->min === $this->max) {
            return $this->boundary === IntervalBoundary::OpenOpen ? 0 : 1;
        }

        $count = $this->max - $this->min - 1;
        if ($this->includesMinimum()) {
            ++$count;
        }
        if ($this->includesMaximum()) {
            ++$count;
        }

        return \max(0, $count);
    }
}
