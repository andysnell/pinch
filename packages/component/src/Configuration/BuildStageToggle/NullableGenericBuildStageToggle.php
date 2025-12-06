<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @template T
 * @implements BuildStageToggle<T|null>
 */
readonly class NullableGenericBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    /**
     * @param T $production
     * @param T $staging
     * @param T $development
     */
    public function __construct(
        public mixed $production = null,
        public mixed $staging = null,
        public mixed $development = null,
    ) {
    }
}
