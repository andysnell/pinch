<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Defines containers that hold values by key, and which allow for overriding
 * already set values. That is, mutating the container state after it has been
 * instantiated. This interface does not make any guarantees about the type of
 * the values held in the container. (Though it does define a generic type T,
 * which could be anything from a scalar, or objects, to mixed). It also does
 * not guarantee that the key is the class name of the value.
 *
 * @template T
 */
#[Contract]
interface MutableContainer extends ContainerInterface
{
    public function has(\Stringable|string $id): bool;

    /**
     * @return T
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(\Stringable|string $id): mixed;

    /**
     * Note: we do not define $value to be of type T, as it is not strictly re
     * for the value set to be the same one you get out. We do this frequently
     * with callables and factories in the service container. The implementations
     * are responsible to make sure that no matter what gets passed in, the
     * value you get out is of type T.
     */
    public function set(\Stringable|string $id, mixed $value): void;

    public function unset(\Stringable|string $id): void;
}
