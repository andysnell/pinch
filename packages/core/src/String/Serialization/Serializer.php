<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\Serialization;

use PhoneBurner\Pinch\Enum\Trait\WithUnitEnumInstanceStaticMethod;

enum Serializer
{
    use WithUnitEnumInstanceStaticMethod;

    case Igbinary;
    case Php;
}
