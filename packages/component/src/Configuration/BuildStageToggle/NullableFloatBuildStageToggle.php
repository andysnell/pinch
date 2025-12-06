<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<float|null>
 */
final readonly class NullableFloatBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    public function __construct(
        public float|null $production = null,
        public float|null $staging = null,
        public float|null $development = null,
    ) {
    }
}
