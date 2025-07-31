<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Memory;

use PhoneBurner\Pinch\Memory\Unit\BinaryMemoryUnit;
use PhoneBurner\Pinch\Memory\Unit\DecimalMemoryUnit;

final readonly class Bytes implements \Stringable, \JsonSerializable
{
    public function __construct(public int $value)
    {
        $this->value >= 0 || throw new \UnexpectedValueException('Bytes must be non-negative integer');
    }

    public function bits(): int
    {
        return $this->value * 8;
    }

    public function convert(
        BinaryMemoryUnit|DecimalMemoryUnit $unit = BinaryMemoryUnit::Mebibyte,
        int $precision = 2,
    ): float {
        return \round($this->value / $unit->value, $precision);
    }

    public function diff(self $bytes): self
    {
        return new self($this->value - $bytes->value);
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        $unit = BinaryMemoryUnit::fit($this->value);
        return \sprintf('%.2f %s', $this->convert($unit), $unit->symbol());
    }
}
