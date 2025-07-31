<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Fixtures\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
final readonly class MockEnumAttribute
{
    public function __construct(public string $value)
    {
    }
}
