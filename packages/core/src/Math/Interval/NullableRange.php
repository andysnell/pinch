<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

/**
 * @template T
 */
interface NullableRange
{
    /**
     * @return T|null
     */
    public function min(): mixed;

    /**
     * @return T|null
     */
    public function max(): mixed;

    public function unbounded(): bool;
}
