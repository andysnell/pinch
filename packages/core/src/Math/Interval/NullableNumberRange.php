<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

use Random\IntervalBoundary;

/**
 * @implements NullableNumericRange<int|float>
 */
final readonly class NullableNumberRange implements NullableNumericRange
{
    public function __construct(
        public int|float|null $min,
        public int|float|null $max,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        if ($this->min !== null && $this->max !== null && $this->max < $this->min) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): int|float|null
    {
        return $this->min;
    }

    public function max(): int|float|null
    {
        return $this->max;
    }

    public function unbounded(): bool
    {
        return $this->min === null || $this->max === null;
    }

    public function contains(int|float $value): bool
    {
        if ($this->min === null && $this->max === null) {
            return true; // Unbounded ranges contain all values
        }

        if ($this->min === null) {
            return match ($this->boundary) {
                IntervalBoundary::ClosedClosed, IntervalBoundary::OpenClosed => $value <= $this->max,
                IntervalBoundary::OpenOpen, IntervalBoundary::ClosedOpen => $value < $this->max,
            };
        }

        if ($this->max === null) {
            return match ($this->boundary) {
                IntervalBoundary::ClosedClosed, IntervalBoundary::ClosedOpen => $value >= $this->min,
                IntervalBoundary::OpenOpen, IntervalBoundary::OpenClosed => $value > $this->min,
            };
        }

        return match ($this->boundary) {
            IntervalBoundary::ClosedClosed => $value >= $this->min && $value <= $this->max,
            IntervalBoundary::OpenOpen => $value > $this->min && $value < $this->max,
            IntervalBoundary::OpenClosed => $value > $this->min && $value <= $this->max,
            IntervalBoundary::ClosedOpen => $value >= $this->min && $value < $this->max,
        };
    }
}
