<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringProhibitsSerialization;
use PhoneBurner\Pinch\String\Encoding\Encoding;

final readonly class MockSerializationProhibitedBinaryString implements BinaryString
{
    use BinaryStringProhibitsSerialization;

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

    public function export(
        Encoding|null $encoding = null,
        bool $prefix = false,
    ): string {
        return 'exported';
    }
}
