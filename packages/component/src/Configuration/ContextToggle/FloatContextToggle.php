<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<float>
 */
final readonly class FloatContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public float $http = 0.0,
        public float $cli = 0.0,
        public float $test = 0.0,
    ) {
    }
}
