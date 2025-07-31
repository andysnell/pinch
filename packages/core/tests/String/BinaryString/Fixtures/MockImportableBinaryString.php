<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\ImportableBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;

final readonly class MockImportableBinaryString implements ImportableBinaryString
{
    use BinaryStringExportBehavior;
    use BinaryStringImportBehavior;

    private string $bytes;

    public function __construct(#[\SensitiveParameter] BinaryString|string $bytes)
    {
        $this->bytes = \is_string($bytes) ? $bytes : $bytes->bytes();
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
