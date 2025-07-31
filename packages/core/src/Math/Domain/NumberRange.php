<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Domain;

use Random\IntervalBoundary;

/**
 * @implements Range<int|float>
 */
final class NumberRange implements Range
{
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
}
