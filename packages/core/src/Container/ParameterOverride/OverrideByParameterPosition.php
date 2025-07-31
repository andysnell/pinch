<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\ParameterOverride;

use PhoneBurner\Pinch\Container\ParameterOverride;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideType;

final readonly class OverrideByParameterPosition implements ParameterOverride
{
    public function __construct(
        public int $position,
        public mixed $value = null,
    ) {
        $this->position >= 0 || throw new \UnexpectedValueException(
            'parameter position identifier must be greater than or equal to zero',
        );
    }

    public function type(): OverrideType
    {
        return OverrideType::Position;
    }

    public function identifier(): int
    {
        return $this->position;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
