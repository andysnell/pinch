<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Memory\Unit;

use PhoneBurner\Pinch\Attribute\UnitName;
use function PhoneBurner\Pinch\Math\int_floor;

enum DecimalMemoryUnit: int
{
    #[UnitName('byte')]
    case Byte = self::BASE ** 0;

    #[UnitName('kilobyte')]
    case Kilobyte = self::BASE ** 1;

    #[UnitName('megabyte')]
    case Megabyte = self::BASE ** 2;

    #[UnitName('gigabyte')]
    case Gigabyte = self::BASE ** 3;

    #[UnitName('terabyte')]
    case Terabyte = self::BASE ** 4;

    #[UnitName('petabyte')]
    case Petabyte = self::BASE ** 5;

    #[UnitName('exabyte')]
    case Exabyte = self::BASE ** 6;

    public const int BASE = 10 ** 3;

    public function symbol(): string
    {
        return match ($this) {
            self::Byte => 'B',
            self::Kilobyte => 'KB',
            self::Megabyte => 'MB',
            self::Gigabyte => 'GB',
            self::Terabyte => 'TB',
            self::Petabyte => 'PB',
            self::Exabyte => 'EB',
        };
    }

    public static function fit(int $value): self
    {
        return $value === 0 ? self::Byte : match (int_floor(\log(\abs($value), self::BASE))) {
            0 => self::Byte,
            1 => self::Kilobyte,
            2 => self::Megabyte,
            3 => self::Gigabyte,
            4 => self::Terabyte,
            5 => self::Petabyte,
            default => self::Exabyte,
        };
    }
}
