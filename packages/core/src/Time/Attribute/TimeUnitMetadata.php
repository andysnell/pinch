<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
class TimeUnitMetadata
{
    public function __construct(
        bool $fixed_length,
        string $symbol,
    ){
    }
}
