<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * IMPORTANT: The difference between this and the MixedBuildStageToggle is that
 * this one has fallback logic if the value is not set for a given stage. For
 * example, if a value is null for development, it will fall back to staging,
 * and then to production.
 *
 * @implements BuildStageToggle<mixed>
 */
final readonly class NullableMixedBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    public function __construct(
        public mixed $production = null,
        public mixed $staging = null,
        public mixed $development = null,
    ) {
    }
}
