<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * IMPORTANT: The difference between this and the MixedBuildStageToggle is that
 * this one does not have fallback logic if the value is not set for a given stage.
 *
 * @implements BuildStageToggle<mixed>
 */
final readonly class MixedBuildStageToggle implements BuildStageToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public mixed $production = null,
        public mixed $staging = null,
        public mixed $development = null,
    ) {
    }
}
