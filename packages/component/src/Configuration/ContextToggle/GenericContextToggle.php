<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T
 * @implements ContextToggle<T>
 */
final readonly class GenericContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param T $http
     * @param T $cli
     * @param T $test
     */
    public function __construct(
        public mixed $http,
        public mixed $cli,
        public mixed $test,
    ) {
    }
}
