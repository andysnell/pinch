<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT | \Attribute::IS_REPEATABLE)]
class DisplayLabel implements \Stringable
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
