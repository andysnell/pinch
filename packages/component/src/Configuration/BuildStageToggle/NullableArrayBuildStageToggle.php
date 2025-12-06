<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @template T of array
 * @implements BuildStageToggle<T|null>
 */
final readonly class NullableArrayBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    /**
     * @param T|null $production
     * @param T|null $staging
     * @param T|null $development
     */
    public function __construct(
        public array|null $production = null,
        public array|null $staging = null,
        public array|null $development = null,
    ) {
    }
}
