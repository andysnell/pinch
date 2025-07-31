<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Domain;

use Random\IntervalBoundary;

/**
 * @template T
 */
interface Range
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
