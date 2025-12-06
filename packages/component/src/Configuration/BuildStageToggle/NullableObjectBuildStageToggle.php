<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @template T of object
 * @implements BuildStageToggle<T|null>
 */
final readonly class NullableObjectBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    /**
     * @param T|null $production
     * @param T|null $staging
     * @param T|null $development
     */
    public function __construct(
        public object|null $production,
        public object|null $staging,
        public object|null $development,
    ) {
    }
}
