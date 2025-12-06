<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<float>
 */
final readonly class FloatBuildStageToggle implements BuildStageToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public float $production = 0.0,
        public float $staging = 0.0,
        public float $development = 0.0,
    ) {
    }
}
