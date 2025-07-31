<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Array;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface Arrayable
{
    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;
}
