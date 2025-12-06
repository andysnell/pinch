<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<bool|null>
 */
final readonly class NullableBoolContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public bool|null $http = null,
        public bool|null $cli = null,
        public bool|null $test = null,
    ) {
    }
}
