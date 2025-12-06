<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T
 * @implements ContextToggle<array<T>|null>
 */
final readonly class NullableArrayContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param array<mixed>|null $http
     * @param array<mixed>|null $cli
     * @param array<mixed>|null $test
     */
    public function __construct(
        public array|null $http = null,
        public array|null $cli = null,
        public array|null $test = null,
    ) {
    }
}
