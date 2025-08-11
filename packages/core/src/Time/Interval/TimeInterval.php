<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Interval;

use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Time\Domain\TimeUnit;
use PhoneBurner\Pinch\Time\Interval\Duration;

use function PhoneBurner\Pinch\Array\array_map_with_key;
use function PhoneBurner\Pinch\Math\int_clamp;

use const PhoneBurner\Pinch\Time\DAYS_IN_WEEK;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_DAY;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_HOUR;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_MILLISECOND;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_MINUTE;
use const PhoneBurner\Pinch\Time\MICROSECONDS_IN_SECOND;

/**
 * An extension of the builtin \DateInterval class for representing well-defined periods
 * of time. Since \DateInterval has variable-length year and month component,
 * the actual amount of time (in terms of clock seconds) is undefined. In contrast,
 * a TimeInterval is an immutable, positive and finite number of microseconds.
 *
 * @implements Arrayable<string, int>
 */
final class TimeInterval extends \DateInterval implements \Stringable, Arrayable
{
    // phpcs:disable
    public int|float $total_days {
        get => $this->microseconds / MICROSECONDS_IN_DAY;
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $total_hours {
        get => $this->microseconds / MICROSECONDS_IN_HOUR;
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $total_minutes {
        get => $this->microseconds / MICROSECONDS_IN_MINUTE;
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $total_seconds {
        get => $this->microseconds / MICROSECONDS_IN_SECOND;
    }
    // phpcs:enable

    // phpcs:disable
    public int $hours {
        get => \intdiv($this->microseconds, MICROSECONDS_IN_HOUR);
    }
    // phpcs:enable

    // phpcs:disable
    public int $minutes {
        get => \intdiv($this->microseconds, MICROSECONDS_IN_MINUTE);
    }
    // phpcs:enable

    // phpcs:disable
    public int $seconds {
        get => \intdiv($this->microseconds, MICROSECONDS_IN_SECOND);
    }
    // phpcs:enable

    // phpcs:disable
    public string $date_string {
        get => \implode(', ', array_map_with_key(
            static fn(int $value, string $unit): string => $value . ' ' . $unit,
            $this->toArray(true) ?: ['seconds' => 0],
        ));
    }
    // phpcs:enable

    // phpcs:disable
    public Duration $duration {
        get => Duration::make($this);
    }
    // phpcs:enable

    public readonly int $microseconds;

    /**
     * Note that we call the parent __unserialize method instead of the constructor
     * to avoid having to generate a new duration string just to have the parent
     * parse it back into the component values. Additionally, \DateInterval cannot
     * handle duration strings with fractional seconds.
     */
    public function __construct(
        int|float $days = 0,
        int|float $hours = 0,
        int|float $minutes = 0,
        int|float $seconds = 0,
        int $microseconds = 0,
    ) {
        $this->microseconds = self::reduce($days, $hours, $minutes, $seconds, $microseconds);
        parent::__unserialize([
            'y' => 0,
            'm' => 0,
            'd' => \intdiv($this->microseconds, MICROSECONDS_IN_DAY),
            'h' => \intdiv($this->microseconds % MICROSECONDS_IN_DAY, MICROSECONDS_IN_HOUR),
            'i' => \intdiv($this->microseconds % MICROSECONDS_IN_HOUR, MICROSECONDS_IN_MINUTE),
            's' => \intdiv($this->microseconds % MICROSECONDS_IN_MINUTE, MICROSECONDS_IN_SECOND),
            'f' => $this->microseconds % MICROSECONDS_IN_SECOND / MICROSECONDS_IN_SECOND,
            'invert' => 0,
            'days' => \intdiv($this->microseconds, MICROSECONDS_IN_DAY) ,
        ]);
    }

    public static function make(mixed $interval, \DateTimeImmutable|null $now = null): self
    {
        return match (true) {
            $interval instanceof parent => self::instance($interval),
            $interval instanceof Duration => self::createFromDuration($interval),
            $interval instanceof \DateTimeInterface => self::until($interval, $now ?? new \DateTimeImmutable()),
            $interval === null => self::max(),
            \is_int($interval) => new self(seconds: $interval),
            \is_numeric($interval) => new self(seconds: (float)$interval),
            default => throw new \InvalidArgumentException('Cannot Convert Value to Time Interval'),
        };
    }

    public static function until(
        \DateTimeInterface $datetime,
        \DateTimeImmutable $now = new \DateTimeImmutable(),
    ): self {
        return self::instance($now->diff($datetime, true));
    }

    public static function instance(parent $interval): self
    {
        self::validateDateInterval($interval);
        return match (true) {
            $interval instanceof self => $interval,
            $interval instanceof parent => new self(
                $interval->d,
                $interval->h,
                $interval->i,
                $interval->s,
                (int)($interval->f * MICROSECONDS_IN_SECOND),
            ),
        };
    }

    public function add(parent $interval): self
    {
        return self::plus(microseconds: self::instance($interval)->microseconds);
    }

    public function sub(parent $interval): self
    {
        return self::minus(microseconds: self::instance($interval)->microseconds);
    }

    public static function max(): self
    {
        return new self(microseconds: \PHP_INT_MAX);
    }

    public static function min(): self
    {
        return new self();
    }

    public function create(
        int|float $days = 0,
        int|float $hours = 0,
        int|float $minutes = 0,
        int|float $seconds = 0,
        int $microseconds = 0,
    ): self {
        return new self($days, $hours, $minutes, $seconds, $microseconds);
    }

    public function plus(
        int|float $days = 0,
        int|float $hours = 0,
        int|float $minutes = 0,
        int|float $seconds = 0,
        int $microseconds = 0,
    ): self {
        $microseconds = self::reduce($days, $hours, $minutes, $seconds, $microseconds);
        return new self(microseconds: $this->microseconds + $microseconds);
    }

    public function minus(
        int|float $days = 0,
        int|float $hours = 0,
        int|float $minutes = 0,
        int|float $seconds = 0,
        int $microseconds = 0,
    ): self {
        $microseconds = self::reduce($days, $hours, $minutes, $seconds, $microseconds);
        return new self(microseconds: \max(0, $this->microseconds - $microseconds));
    }

    private static function createFromDuration(Duration|\Stringable|string $duration): self
    {
        $duration = $duration instanceof Duration ? $duration : Duration::instance($duration);

        foreach (['years', 'months'] as $unit) {
            $duration->$unit === 0 || throw new \UnexpectedValueException(\sprintf('non-zero %s property', $unit));
        }

        return new self(
            days: ($duration->weeks * DAYS_IN_WEEK) + $duration->days,
            hours: $duration->hours,
            minutes: $duration->minutes,
            seconds: $duration->seconds,
            microseconds: $duration->microseconds,
        );
    }

    public function compare(self $interval): int
    {
        return $this->microseconds <=> $interval->microseconds;
    }

    public function format(string $format): string
    {
        return parent::format($format);
    }

    public function toDecimalUnit(TimeUnit $unit, int $places = 2): string
    {
        return \bcdiv((string)$this->microseconds, (string)match ($unit) {
            TimeUnit::Day => MICROSECONDS_IN_DAY,
            TimeUnit::Hour => MICROSECONDS_IN_HOUR,
            TimeUnit::Minute => MICROSECONDS_IN_MINUTE,
            TimeUnit::Second => MICROSECONDS_IN_SECOND,
            TimeUnit::Millisecond => MICROSECONDS_IN_MILLISECOND,
            TimeUnit::Microsecond => 1,
            default => throw new \InvalidArgumentException('Invalid Time Unit'),
        }, $places);
    }

    public function toArray(bool $filter_empty_units = false): array
    {
        return $filter_empty_units ? \array_filter($this->toArray(false)) : [
            'days' => $this->d,
            'hours' => $this->h,
            'minutes' => $this->i,
            'seconds' => $this->s,
            'microseconds' => $this->microseconds,
        ];
    }

    public function __toString(): string
    {
        return (string)$this->duration;
    }

    public function __serialize(): array
    {
        return [$this->microseconds];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct(microseconds: $data[0]);
    }

    private static function reduce(
        int|float $days = 0,
        int|float $hours = 0,
        int|float $minutes = 0,
        int|float $seconds = 0,
        int $microseconds = 0,
    ): int {
        if ($days) {
            $microseconds += $days * MICROSECONDS_IN_DAY;
        }

        if ($hours) {
            $microseconds += $hours * MICROSECONDS_IN_HOUR;
        }

        if ($minutes) {
            $microseconds += $minutes * MICROSECONDS_IN_MINUTE;
        }

        if ($seconds) {
            $microseconds += $seconds * MICROSECONDS_IN_SECOND;
        }

        return int_clamp($microseconds, 0, \PHP_INT_MAX);
    }

    private static function validateDateInterval(parent $interval): void
    {
        if ($interval->y !== 0 || $interval->m !== 0) {
            throw new \UnexpectedValueException(
                'Cannot Create a TimeInterval from DateInterval with Non-Zero Year or Month Values',
            );
        }
    }
}
