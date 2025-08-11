<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Interval;

use PhoneBurner\Pinch\Math\Interval\Range;
use PhoneBurner\Pinch\Time\Interval\NullableDateTimeRange;

/**
 * @template T of \DateTimeImmutable
 * @extends Range<T>
 * @extends NullableDateTimeRange<T>
 */
interface DateTimeRange extends NullableDateTimeRange, Range
{
    public function min(): \DateTimeImmutable;

    public function max(): \DateTimeImmutable;
}
