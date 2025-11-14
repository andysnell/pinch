<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\Exception as CarbonException;
use PhoneBurner\Pinch\Time\TimeZone\Tz;

/**
 * Note: this function is similar to `cast_nullable_datetime()`, but will
 * but is more forgiving with the input type, and does not have the same
 * type assertion behavior. By default, the timezone of the returned instance
 * will be the same as the input value, if the input value had a timezone component.
 * If the input is an integer, the resulting timestamp will be in UTC.
 *
 * If a timezone is provided, it will be used to set the timezone of the returned instance.
 * This is probably most useful for parsing timestamps (which would always be in UTC) into a specific timezone.
 *
 * @phpstan-return ($value is \DateTimeInterface ? \DateTimeImmutable : \DateTimeImmutable|null)
 */
function parse_datetime(mixed $value, \DateTimeZone|Tz|null $timezone = null): \DateTimeImmutable|null
{
    try {
        $value = $value instanceof \DateTimeImmutable ? $value : match (\gettype($value)) {
            'object' => $value instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($value) : null,
            'integer', 'double' => \DateTimeImmutable::createFromTimestamp($value),
            'string' => match ($value) {
                '0000-00-00 00:00:00', '' => null,
                default => new \DateTimeImmutable($value),
            },
            default => null,
        };

        if ($value === null || $timezone === null) {
            return $value;
        }

        return $value->setTimezone($timezone instanceof Tz ? $timezone->timezone() : $timezone);
    } catch (\DateException) {
        return null;
    }
}

/**
 * Note: this function is similar to `cast_nullable_datetime()`, but will
 * but is more forgiving with the input type, and does not have the same
 * type assertion behavior.
 */
function parse_carbon(mixed $value, \DateTimeZone|Tz|null $timezone = null): CarbonImmutable|null
{
    try {
        $value = $value instanceof CarbonImmutable ? $value : match (\gettype($value)) {
            'object' => $value instanceof \DateTimeInterface ? CarbonImmutable::instance($value) : null,
            'integer', 'double' => CarbonImmutable::createFromTimestampUTC($value),
            'string' => match ($value) {
                '0000-00-00 00:00:00', '' => null,
                default => new CarbonImmutable($value),
            },
            default => null,
        };

        if ($value === null || $timezone === null) {
            return $value;
        }

        return $value->setTimezone($timezone instanceof Tz ? $timezone->timezone() : $timezone);
    } catch (\DateException | CarbonException) {
        return null;
    }
}
