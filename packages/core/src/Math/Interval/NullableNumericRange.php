<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

/**
 * @template T of int|float
 * @extends NullableRange<T>
 */
interface NullableNumericRange extends NullableRange
{
    public function min(): int|float|null;

    public function max(): int|float|null;

    public function contains(int|float $value): bool;
}
