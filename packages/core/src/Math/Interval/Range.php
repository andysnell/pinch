<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

use Random\IntervalBoundary;

/**
 * @template T
 * @extends NullableRange<T>
 */
interface Range extends NullableRange
{
    // phpcs:ignore
    public IntervalBoundary $boundary { get; }

    /**
     * @return T
     */
    public function min(): mixed;

    /**
     * @return T
     */
    public function max(): mixed;
}
