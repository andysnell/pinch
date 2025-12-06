<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<string|null>
 */
final readonly class NullableStringContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    public function __construct(
        public string|null $http = null,
        public string|null $cli = null,
        public string|null $test = null,
    ) {
    }
}
