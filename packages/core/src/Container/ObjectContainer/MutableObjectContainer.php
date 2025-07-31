<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\ObjectContainer;

use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Collections\Map\HasMutableContainerArrayableBehavior;
use PhoneBurner\Pinch\Collections\Map\HasMutableContainerArrayAccessBehavior;
use PhoneBurner\Pinch\Collections\MapCollection;
use PhoneBurner\Pinch\Container\Exception\NotFound;
use PhoneBurner\Pinch\Container\InvokingContainer;
use PhoneBurner\Pinch\Container\InvokingContainer\HasInvokingContainerBehavior;
use PhoneBurner\Pinch\Container\MutableContainer;
use PhoneBurner\Pinch\Container\ObjectContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function PhoneBurner\Pinch\Type\get_debug_value;

/**
 * @template T of object
 * @implements Arrayable<string, T>
 * @implements MutableContainer<T>
 * @implements ObjectContainer<T>
 * @implements \ArrayAccess<string, T>
 * @implements \IteratorAggregate<string, T>
 */
#[Contract]
class MutableObjectContainer implements
    Arrayable,
    InvokingContainer,
    MutableContainer,
    ObjectContainer,
    \ArrayAccess,
    \Countable,
    \IteratorAggregate
{
    /**
     * @use HasMutableContainerArrayableBehavior<T>
     */
    use HasMutableContainerArrayableBehavior;
    use HasInvokingContainerBehavior;
    use HasMutableContainerArrayAccessBehavior;

    /** @param array<string, T> $entries */
    public function __construct(protected array $entries = [])
    {
    }

    /**
     * @return T&object
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(\Stringable|string $id): object
    {
        return $this->entries[(string)$id] ?? throw new NotFound();
    }

    /**
     * Note: we do not define $value to be of type T, as it is not strictly re
     * for the value set to be the same one you get out. We do this frequently
     * with callables and factories in the service container. The implementations
     * are responsible to ensure that no matter what gets passed in, the
     * value you get out is of type T.
     *
     * @phpstan-assert T $value
     */
    public function set(\Stringable|string $id, mixed $value): void
    {
        if (! \is_object($value)) {
            throw new \InvalidArgumentException(
                \sprintf('Value must be an object, got id: %s, value: %s', $id, get_debug_value($value)),
            );
        }
        /** @var T $value */
        $this->entries[(string)$id] = $value;
    }

    public function unset(\Stringable|string $id): void
    {
        unset($this->entries[(string)$id]);
    }

    public function has(\Stringable|string $id): bool
    {
        return isset($this->entries[(string)$id]);
    }

    /**
     * @param array<string, T>|MapCollection<T> $map
     */
    public function replace(array|MapCollection $map): static
    {
        $this->entries = $map instanceof MapCollection ? $map->toArray() : $map;
        return $this;
    }

    public function clear(): void
    {
        $this->entries = [];
    }

    /**
     * @return array<string, T>
     */
    public function toArray(): array
    {
        return $this->entries;
    }
}
