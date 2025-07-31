<?php

// phpcs:disable SlevomatCodingStandard.Classes.TraitUseSpacing.IncorrectLinesCountAfterLastUse


declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Trait\Fixtures;

use PhoneBurner\Pinch\Trait\HasNonInstantiableBehavior;

readonly class ChildClassUsingTrait
{
    use HasNonInstantiableBehavior;

    // Test that the trait works independently in different classes
}
