<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Interval;

use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Random\IntervalBoundary;

use function PhoneBurner\Pinch\Time\parse_carbon;
use function PhoneBurner\Pinch\Type\get_debug_value;

/**
 * @implements DateTimeRange<CarbonImmutable>
 */
final class CarbonImmutableRange implements DateTimeRange
{
    public CarbonImmutable $start;

    public CarbonImmutable $end;

    /**
     * @param IntervalBoundary $boundary default includes both endpoints in interval
     */
    public function __construct(
        \DateTimeInterface|string|int|float $start,
        \DateTimeInterface|string|int|float $end,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        $this->start = parse_carbon($start) ?? throw new \UnexpectedValueException(
            'invalid start date: ' . get_debug_value($start),
        );

        $this->end = parse_carbon($end) ?? throw new \UnexpectedValueException(
            'invalid end date: ' . get_debug_value($end),
        );

        if ($this->end < $this->start) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): CarbonImmutable
    {
        return $this->start;
    }

    public function max(): CarbonImmutable
    {
        return $this->end;
    }

    public function period(\DateInterval $interval = new TimeInterval(days: 1)): CarbonPeriod
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

        return new CarbonPeriod(
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
