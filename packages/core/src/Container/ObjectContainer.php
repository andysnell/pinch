<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Defines containers that hold objects by key. This interface does not
 * make any guarantees about the mutability of the container or whether it will
 * attempt to auto-wire undefined objects. It does not guarantee that the key
 * is the class name of the object. (Which is one reason we do not define the
 * template type for TKey here -- it would be too restrictive.)
 *
 * @template T of object
 */
#[Contract]
interface ObjectContainer extends ContainerInterface
{
    public function has(\Stringable|string $id): bool;

    /**
     * @return T
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(\Stringable|string $id): object;
}
