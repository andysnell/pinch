<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<bool|null>
 */
final readonly class NullableBoolBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    public function __construct(
        public bool|null $production = null,
        public bool|null $staging = null,
        public bool|null $development = null,
    ) {
    }
}
