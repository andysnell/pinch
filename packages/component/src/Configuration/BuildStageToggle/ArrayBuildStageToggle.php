<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @template T of array
 * @implements BuildStageToggle<T>
 */
final readonly class ArrayBuildStageToggle implements BuildStageToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param T $production
     * @param T $staging
     * @param T $development
     */
    public function __construct(
        public array $production = [],
        public array $staging = [],
        public array $development = [],
    ) {
    }
}
