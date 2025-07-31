<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Fixtures;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\Encoding\Encoding;

final readonly class TestBinaryString implements BinaryString
{
    public function __construct(
        private string $bytes,
    ) {
    }

    public function bytes(): string
    {
        return $this->bytes;
    }

    public function length(): int
    {
        return \strlen($this->bytes);
    }

    public function export(Encoding|null $encoding = null, bool $prefix = false): string
    {
        return $this->bytes;
    }

    public function __toString(): string
    {
        return $this->bytes;
    }

    public function jsonSerialize(): string
    {
        return $this->bytes;
    }
}
