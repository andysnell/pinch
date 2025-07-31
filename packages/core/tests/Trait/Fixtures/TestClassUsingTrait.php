<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Trait\Fixtures;

use PhoneBurner\Pinch\Trait\HasNonInstantiableBehavior;

final readonly class TestClassUsingTrait
{
    use HasNonInstantiableBehavior;

    // Static method to verify class is useful despite being non-instantiable
    public static function staticMethod(): string
    {
        return 'This class provides static functionality';
    }
}
