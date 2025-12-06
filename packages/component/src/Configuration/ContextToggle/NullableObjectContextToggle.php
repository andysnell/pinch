<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T of object
 * @implements ContextToggle<T|null>
 */
final readonly class NullableObjectContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param T|null $http
     * @param T|null $cli
     * @param T|null $test
     */
    public function __construct(
        public object|null $http = null,
        public object|null $cli = null,
        public object|null $test = null,
    ) {
    }
}
