<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute;

#[\Attribute]
final class UnitName
{
    public readonly string $plural;

    public function __construct(
        public readonly string $name,
        string|null $plural = null,
    ) {
        $name !== '' || throw new \UnexpectedValueException('unit name cannot be empty');
        $this->plural = $plural ?? $name . 's';
    }
}
