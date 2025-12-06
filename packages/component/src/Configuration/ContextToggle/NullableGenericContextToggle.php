<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T
 * @implements ContextToggle<T|null>
 */
final readonly class NullableGenericContextToggle implements ContextToggle
{
    use TogglesWithoutFallbackBehavior;

    /**
     * @param T|null $http
     * @param T|null $cli
     * @param T|null $test
     */
    public function __construct(
        public mixed $http,
        public mixed $cli,
        public mixed $test,
    ) {
    }
}
