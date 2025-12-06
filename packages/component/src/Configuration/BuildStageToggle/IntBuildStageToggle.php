<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<int>
 */
final readonly class IntBuildStageToggle implements BuildStageToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public int $production = 0,
        public int $staging = 0,
        public int $development = 0,
    ) {
    }
}
