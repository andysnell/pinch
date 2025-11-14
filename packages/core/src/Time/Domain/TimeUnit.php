<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Domain;

use PhoneBurner\Pinch\Attribute\UnitName;
use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Time\Attribute\TimeUnitMetadata;

/**
 * Note: we're following the example of the ISO 8601 standard and treating
 * "days" as a fixed time unit equal to exactly 24 hours (or 86,400 seconds).
 * However, if considered in a "calendar" context, a day may have variable
 * length due to daylight savings time or leap seconds (pre-2035).
 *
 * While the second may be the base unit of time in the SI/metric system,
 * we will generally treat microseconds as the base unit for measuring time.
 * This aligns with how time is represented most of the built-in PHP objects/functions,
 * excluding high-resolution timing functions that operate on nanoseconds. It also
 * aligns with the "fractional seconds" component of ISO 8601 datetime and duration
 * strings, which allow up to six decimal places. Thus, for the most part, we can
 * ignore the nanosecond and millisecond as distinct cases and just handle the
 * conversion where necessary. There is a technical lost of precision with this approach;
 * however, considering light takes over 5 microseconds to travel a mile in a
 * vacuum, it's not significant by any means.
 */
#[Internal]
enum TimeUnit
{
    #[TimeUnitMetadata(fixed_length: false, symbol: 'y')]
    #[UnitName('year')]
    case Year;

    #[TimeUnitMetadata(fixed_length: false, symbol: 'mo')]
    #[UnitName('month')]
    case Month;

    #[TimeUnitMetadata(fixed_length: false, symbol: 'wk')]
    #[UnitName('week')]
    case Week;

    #[TimeUnitMetadata(fixed_length: true, symbol: 'd')]
    #[UnitName('day')]
    case Day;

    #[TimeUnitMetadata(fixed_length: true, symbol: 'hr')]
    #[UnitName('hour')]
    case Hour;

    #[TimeUnitMetadata(fixed_length: true, symbol: 'min')]
    #[UnitName('minute')]
    case Minute;

    #[TimeUnitMetadata(fixed_length: true, symbol: 's')]
    #[UnitName('second')]
    case Second;

    #[TimeUnitMetadata(fixed_length: true, symbol: 'ms')]
    #[UnitName('millisecond')]
    case Millisecond;

    #[TimeUnitMetadata(fixed_length: true, symbol: 'μs')]
    #[UnitName('microsecond')]
    case Microsecond;

    #[TimeUnitMetadata(fixed_length: true, symbol: 'ns')]
    #[UnitName('nanosecond')]
    case Nanosecond;

    public function isFixedLengthUnit(): bool
    {
        return $this !== self::Year && $this !== self::Month;
    }
}
