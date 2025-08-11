<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

use Random\IntervalBoundary;

/**
 * @implements NumericRange<int|float>
 */
final readonly class NumberRange implements NumericRange
{
    /**
     * @param IntervalBoundary $boundary default includes both endpoints in interval
     */
    public function __construct(
        public int|float $min,
        public int|float $max,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        if ($this->max < $this->min) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): int|float
    {
        return $this->min;
    }

    public function max(): int|float
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

    public function unbounded(): false
    {
        return false;
    }
}
