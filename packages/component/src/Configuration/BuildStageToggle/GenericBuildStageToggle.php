<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @template T
 * @implements BuildStageToggle<T>
 */
readonly class GenericBuildStageToggle implements BuildStageToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param T $production
     * @param T $staging
     * @param T $development
     */
    public function __construct(
        public mixed $production,
        public mixed $staging,
        public mixed $development,
    ) {
    }
}
