<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @implements BuildStageToggle<int|null>
 */
final readonly class NullableIntBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    public function __construct(
        public int|null $production = null,
        public int|null $staging = null,
        public int|null $development = null,
    ) {
    }
}
