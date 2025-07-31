<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\BinaryString\Traits;

trait BinaryStringFromRandomBytes
{
    public static function generate(): self
    {
        return new self(\random_bytes(self::LENGTH));
    }
}
