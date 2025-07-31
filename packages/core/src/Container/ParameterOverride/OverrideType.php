<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\ParameterOverride;

enum OverrideType
{
    case Position;
    case Name;
    case Hint;
}
