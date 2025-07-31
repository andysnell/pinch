<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringFromRandomBytes;

final readonly class MockGeneratableBinaryString implements BinaryString
{
    use BinaryStringExportBehavior;
    use BinaryStringFromRandomBytes;

    public const int LENGTH = 16;

    public function __construct(private string $bytes)
    {
    }

    public function bytes(): string
    {
        return $this->bytes;
    }

    public function length(): int
    {
        return \strlen($this->bytes);
    }

    public function __toString(): string
    {
        return $this->export();
    }

    public function jsonSerialize(): string
    {
        return $this->export();
    }
}
