<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Fixtures;

class StringWrapper implements \Stringable
{
    public function __construct(public readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
