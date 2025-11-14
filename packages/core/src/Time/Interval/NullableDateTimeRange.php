<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Interval;

use PhoneBurner\Pinch\Math\Interval\NullableRange;

/**
 * @template T of \DateTimeImmutable
 * @extends NullableRange<T>
 */
interface NullableDateTimeRange extends NullableRange
{
    // phpcs:ignore
    public \DateTimeImmutable|null $start { get; }

    // phpcs:ignore
    public \DateTimeImmutable|null $end { get; }

    public function min(): \DateTimeImmutable|null;

    public function max(): \DateTimeImmutable|null;

    public function contains(\DateTimeInterface $value): bool;
}
