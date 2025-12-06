<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

use function PhoneBurner\Pinch\Array\array_get;

final readonly class ImmutableConfiguration implements Configuration
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(public array $values = [])
    {
    }

    public function has(string $id): bool
    {
        return $this->get($id) !== null;
    }

    /**
     * Gets a configuration value by a dot-notation key, returning null if no value is set.
     * Notes:
     *  - We try to match the exact key string first before trying dot notation,
     *  - Keys containing dots, like URLs or email addresses are going to be
     *    problematic. It's ok to use these as values and as keys in nested arrays
     *    that are always accessed together.
     */
    #[\Override]
    public function get(string $id): mixed
    {
        return array_get($id, $this->values);
    }
}
