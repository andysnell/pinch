<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Collections\Map;

use PhoneBurner\Pinch\Container\MutableContainer;
use PhoneBurner\Pinch\Exception\InvalidStringableOffset;

use function PhoneBurner\Pinch\Type\is_stringable;

/**
 * @phpstan-require-implements \ArrayAccess
 * @phpstan-require-implements MutableContainer
 */
trait HasMutableContainerArrayAccessBehavior
{
    public function offsetExists(mixed $offset): bool
    {
        return is_stringable($offset) && $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        is_stringable($offset) || throw new InvalidStringableOffset($offset);
        /** @phpstan-ignore return.type */
        return $this->has($offset) ? $this->get($offset) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        is_stringable($offset) || throw new InvalidStringableOffset($offset);
        $this->set((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        is_stringable($offset) || throw new InvalidStringableOffset($offset);
        $this->unset($offset);
    }
}
