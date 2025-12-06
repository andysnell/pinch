<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @template T of object
 * @implements BuildStageToggle<T>
 */
final readonly class ObjectBuildStageToggle implements BuildStageToggle
{
    use TogglesWithStandardFallbackBehavior;

    /**
     * @param T $production
     * @param T $staging
     * @param T $development
     */
    public function __construct(
        public object $production,
        public object $staging,
        public object $development,
    ) {
    }
}
