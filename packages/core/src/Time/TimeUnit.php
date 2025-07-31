<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time;

use PhoneBurner\Pinch\Attribute\Usage\Internal;

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
    case Year;
    case Month;
    case Week;
    case Day;
    case Hour;
    case Minute;
    case Second;
    case Millisecond;
    case Microsecond;
    case Nanosecond;

    public function isFixedLengthUnit(): bool
    {
        return $this !== self::Year && $this !== self::Month;
    }
}
