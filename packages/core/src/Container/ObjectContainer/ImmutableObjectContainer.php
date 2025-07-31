<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\ObjectContainer;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Container\Exception\NotFound;
use PhoneBurner\Pinch\Container\InvokingContainer;
use PhoneBurner\Pinch\Container\InvokingContainer\HasInvokingContainerBehavior;
use PhoneBurner\Pinch\Container\ObjectContainer;
use PhoneBurner\Pinch\Exception\InvalidStringableOffset;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function PhoneBurner\Pinch\Type\is_stringable;

/**
 * @template T of object
 * @implements ObjectContainer<T>
 * @implements \IteratorAggregate<T>
 * @implements \ArrayAccess<string, T>
 */
#[Contract]
final readonly class ImmutableObjectContainer implements
    ObjectContainer,
    InvokingContainer,
    \Countable,
    \IteratorAggregate,
    \ArrayAccess
{
    use HasInvokingContainerBehavior;

    /**
     * @param array<string, T> $entries
     */
    public function __construct(private array $entries)
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

    public function has(\Stringable|string $id): bool
    {
        return isset($this->entries[(string)$id]);
    }

    /**
     * @return array<string>
     */
    public function keys(): array
    {
        return \array_keys($this->entries);
    }

    public function getIterator(): \Generator
    {
        yield from $this->entries;
    }

    public function count(): int
    {
        return \count($this->entries);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_stringable($offset) && $this->has((string)$offset);
    }

    /**
     * @return T&object
     */
    public function offsetGet(mixed $offset): object
    {
        is_stringable($offset) || throw new InvalidStringableOffset($offset);
        return $this->entries[$offset] ?? throw new NotFound();
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Container is Immutable and Readonly');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Container is Immutable and Readonly');
    }
}
