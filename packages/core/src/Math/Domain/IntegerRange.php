<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Domain;

use Random\IntervalBoundary;

/** @implements Range<int> */
final class IntegerRange implements Range
{
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
}
