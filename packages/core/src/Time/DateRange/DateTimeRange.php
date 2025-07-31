<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\DateRange;

use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;
use Random\IntervalBoundary;

/** @implements DateRangeInterface<\DateTimeImmutable> */
final class DateTimeRange implements DateRangeInterface
{
    public \DateTimeImmutable $min;

    public \DateTimeImmutable $max;

    public function __construct(
        \DateTimeInterface $min,
        \DateTimeInterface $max,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        $this->min = $min instanceof \DateTimeImmutable ? $min : \DateTimeImmutable::createFromInterface($min);
        $this->max = $max instanceof \DateTimeImmutable ? $max : \DateTimeImmutable::createFromInterface($max);
        if ($this->max < $this->min) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): \DateTimeImmutable
    {
        return $this->min;
    }

    public function max(): \DateTimeImmutable
    {
        return $this->max;
    }

    /**
     * @return \DatePeriod<\DateTimeImmutable,\DateTimeImmutable, null>
     */
    public function period(\DateInterval $interval = new TimeInterval(days: 1)): \DatePeriod
    {
        $boundary = 0;
        if ($this->boundary === IntervalBoundary::ClosedClosed || $this->boundary === IntervalBoundary::OpenClosed) {
            $boundary |= \DatePeriod::INCLUDE_END_DATE;
        }

        if ($this->boundary === IntervalBoundary::ClosedOpen || $this->boundary === IntervalBoundary::OpenOpen) {
            $boundary |= \DatePeriod::EXCLUDE_START_DATE;
        }

        return new \DatePeriod(
            start: $this->min,
            interval: $interval,
            end: $this->max,
            options: $boundary,
        );
    }
}
