<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<int>
 */
final readonly class IntContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public int $http = 0,
        public int $cli = 0,
        public int $test = 0,
    ) {
    }
}
