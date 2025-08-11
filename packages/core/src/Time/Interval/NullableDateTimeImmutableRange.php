<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Interval;

use Random\IntervalBoundary;

/**
 * @implements NullableDateTimeRange<\DateTimeImmutable>
 */
final class NullableDateTimeImmutableRange implements NullableDateTimeRange
{
    public \DateTimeImmutable|null $start;

    public \DateTimeImmutable|null $end;

    /**
     * @param IntervalBoundary $boundary default includes both endpoints in interval
     */
    public function __construct(
        \DateTimeInterface|null $start,
        \DateTimeInterface|null $end,
        public IntervalBoundary $boundary = IntervalBoundary::ClosedClosed,
    ) {
        $this->start = match (true) {
            $start === null => null,
            $start instanceof \DateTimeImmutable => $start,
            default => \DateTimeImmutable::createFromInterface($start),
        };

        $this->end = match (true) {
            $end === null => null,
            $end instanceof \DateTimeImmutable => $end,
            default => \DateTimeImmutable::createFromInterface($end),
        };

        if ($this->start !== null && $this->end !== null && $this->end < $this->start) {
            throw new \UnexpectedValueException('max must be greater than or equal to min');
        }
    }

    public function min(): \DateTimeImmutable|null
    {
        return $this->start;
    }

    public function max(): \DateTimeImmutable|null
    {
        return $this->end;
    }

    public function contains(\DateTimeInterface $value): bool
    {
        if ($this->start === null && $this->end === null) {
            return true; // Unbounded ranges contain all values
        }

        if ($this->start === null) {
            return match ($this->boundary) {
                IntervalBoundary::ClosedClosed, IntervalBoundary::OpenClosed => $value <= $this->end,
                IntervalBoundary::OpenOpen, IntervalBoundary::ClosedOpen => $value < $this->end,
            };
        }

        if ($this->end === null) {
            return match ($this->boundary) {
                IntervalBoundary::ClosedClosed, IntervalBoundary::ClosedOpen => $value >= $this->start,
                IntervalBoundary::OpenOpen, IntervalBoundary::OpenClosed => $value > $this->start,
            };
        }

        return match ($this->boundary) {
            IntervalBoundary::ClosedClosed => $value >= $this->start && $value <= $this->end,
            IntervalBoundary::OpenOpen => $value > $this->start && $value < $this->end,
            IntervalBoundary::OpenClosed => $value > $this->start && $value <= $this->end,
            IntervalBoundary::ClosedOpen => $value >= $this->start && $value < $this->end,
        };
    }

    public function unbounded(): bool
    {
        return $this->start === null || $this->end === null;
    }
}
