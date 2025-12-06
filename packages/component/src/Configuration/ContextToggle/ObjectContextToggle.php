<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T of object
 * @implements ContextToggle<T>
 */
final readonly class ObjectContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param T $http
     * @param T $cli
     * @param T $test
     */
    public function __construct(
        public object $http,
        public object $cli,
        public object $test,
    ) {
    }
}
