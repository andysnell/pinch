<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<int|float|null>
 */
final readonly class NullableNumberContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public int|float|null $http = null,
        public int|float|null $cli = null,
        public int|float|null $test = null,
    ) {
    }
}
