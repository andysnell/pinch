<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Domain;

use function PhoneBurner\Pinch\Type\get_debug_value;

enum Day: int
{
    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    public static function instance(mixed $value): self
    {
        return self::parse($value) ?? throw new \UnexpectedValueException(
            \sprintf("Invalid month value, got: %s", get_debug_value($value)),
        );
    }

    public static function parse(mixed $value): self|null
    {
        return match (true) {
            $value instanceof self, $value === null => $value,
            $value instanceof \DateTimeInterface => self::{$value->format('l')},
            \is_numeric($value) => self::tryFrom((int)$value),
            \is_string($value) => match (\strtolower(\substr($value, 0, 3))) {
                'sun' => self::Sunday,
                'mon' => self::Monday,
                'tue' => self::Tuesday,
                'wed' => self::Wednesday,
                'thu' => self::Thursday,
                'fri' => self::Friday,
                'sat' => self::Saturday,
                default => null,
            },
            default => null,
        };
    }

    /**
     * ISO 8601 considers Monday as 1 and Sunday as 7
     */
    public function toIso8601(): int
    {
        return $this->value ?: 7;
    }
}
