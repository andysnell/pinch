<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<int|float>
 */
final readonly class NumberContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public int|float $http = 0,
        public int|float $cli = 0,
        public int|float $test = 0,
    ) {
    }
}
