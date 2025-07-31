<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Defines a container that holds objects indexed by their fully qualified class
 * name. Note that this interface cannot extend PSR-11's ContainerInterface, as
 * it does not guarantee that the key is the class name of the object.
 *
 * @template T of object
 */
#[Contract]
interface ClassStringObjectContainer
{
    /**
     * @param \Stringable|string $id The method signature is intentionally kept open here,
     * without enforcing the class-string<T> type here, since a non-class-string
     * value passed as the $id should always safely return false,
     */
    public function has(\Stringable|string $id): bool;

    /**
     * @param class-string<T> $id
     * @return T
     * @phpstan-assert class-string<T> $id
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(\Stringable|string $id): object;

    public function set(\Stringable|string $id, mixed $value): void;

    public function unset(\Stringable|string $id): void;
}
