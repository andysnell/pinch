<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Collections\Map;

use PhoneBurner\Pinch\Collections\Map\GenericMapCollection;
use PhoneBurner\Pinch\String\Serialization\PhpSerializable;

/**
 * This is the general purpose implementation of Map, which is as a simple
 * key-value store, with mixed value keys.
 *
 * @extends GenericMapCollection<mixed>
 * @implements PhpSerializable<array<string,mixed>>
 */
final class KeyValueStore extends GenericMapCollection implements PhpSerializable
{
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}
