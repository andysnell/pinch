<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<bool>
 */
final readonly class BoolBuildStageToggle implements BuildStageToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public bool $production = false,
        public bool $staging = false,
        public bool $development = false,
    ) {
    }
}
