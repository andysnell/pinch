<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\BinaryString\Traits;

use PhoneBurner\Pinch\String\BinaryString\ImportableBinaryString;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * @phpstan-require-implements ImportableBinaryString
 */
trait BinaryStringImportBehavior
{
    public static function import(
        #[\SensitiveParameter] string $string,
        Encoding|null $encoding = null,
    ): static {
        return new static(ConstantTimeEncoder::decode($encoding ?? static::DEFAULT_ENCODING, $string));
    }

    public static function tryImport(
        #[\SensitiveParameter] string|null $string,
        Encoding|null $encoding = null,
    ): static|null {
        try {
            return $string ? static::import($string, $encoding) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
