<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Interval;

/**
 * @template T of int|float
 * @extends NullableNumericRange<T>
 * @extends Range<T>
 */
interface NumericRange extends NullableNumericRange, Range
{
    public function min(): int|float;

    public function max(): int|float;
}
