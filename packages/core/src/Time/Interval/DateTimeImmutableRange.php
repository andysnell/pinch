<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Interval;

use Random\IntervalBoundary;

/**
 * @implements DateTimeRange<\DateTimeImmutable>
 */
final class DateTimeImmutableRange implements DateTimeRange
{
    public \DateTimeImmutable $start;

    public \DateTimeImmutable $end;

    /**
     * @param IntervalBoundary $boundary default includes both endpoints in interval
     */
    public function __construct(
        \DateTimeInterface $start,
        \DateTimeInterface $end,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        $this->start = $start instanceof \DateTimeImmutable ? $start : \DateTimeImmutable::createFromInterface($start);
        $this->end = $end instanceof \DateTimeImmutable ? $end : \DateTimeImmutable::createFromInterface($end);
        if ($this->end < $this->start) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): \DateTimeImmutable
    {
        return $this->start;
    }

    public function max(): \DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @return \DatePeriod<\DateTimeImmutable,\DateTimeImmutable, null>
     */
    public function period(\DateInterval $interval = new TimeInterval(days: 1)): \DatePeriod
    {
        $options = 0;

        // Include end date if the interval is closed on the end
        if ($this->boundary === IntervalBoundary::ClosedClosed || $this->boundary === IntervalBoundary::OpenClosed) {
            $options |= \DatePeriod::INCLUDE_END_DATE;
        }

        // Exclude start date if the interval is open on the start
        if ($this->boundary === IntervalBoundary::OpenClosed || $this->boundary === IntervalBoundary::OpenOpen) {
            $options |= \DatePeriod::EXCLUDE_START_DATE;
        }

        return new \DatePeriod(
            start: $this->start,
            interval: $interval,
            end: $this->end,
            options: $options,
        );
    }

    public function contains(\DateTimeInterface $value): bool
    {
        return match ($this->boundary) {
            IntervalBoundary::ClosedClosed => $value >= $this->start && $value <= $this->end,
            IntervalBoundary::OpenOpen => $value > $this->start && $value < $this->end,
            IntervalBoundary::OpenClosed => $value > $this->start && $value <= $this->end,
            IntervalBoundary::ClosedOpen => $value >= $this->start && $value < $this->end,
        };
    }

    public function unbounded(): false
    {
        return false;
    }
}
