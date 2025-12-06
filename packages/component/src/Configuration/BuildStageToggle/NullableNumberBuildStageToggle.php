<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<int|float|null>
 */
final readonly class NullableNumberBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    public function __construct(
        public int|float|null $production = null,
        public int|float|null $staging = null,
        public int|float|null $development = null,
    ) {
    }
}
