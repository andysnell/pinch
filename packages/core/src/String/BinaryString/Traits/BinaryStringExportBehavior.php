<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\BinaryString\Traits;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * @phpstan-require-implements BinaryString
 */
trait BinaryStringExportBehavior
{
    public function export(
        Encoding|null $encoding = null,
        bool $prefix = false,
    ): string {
        return ConstantTimeEncoder::encode($encoding ?? static::DEFAULT_ENCODING, $this->bytes(), $prefix);
    }
}
