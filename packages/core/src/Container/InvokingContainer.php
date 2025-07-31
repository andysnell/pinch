<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideCollection;
use Psr\Container\ContainerInterface;

/**
 * Defines containers that know how to call methods on objects they contain with
 * the ability to override parameters. This interface does not make any guarantees
 * about the mutability of the container or whether it will attempt to auto-wire
 * undefined objects.
 */
#[Contract]
interface InvokingContainer extends ContainerInterface
{
    /**
     * Call a method on an object resolved from this container instance. If a
     * method is not provided, the object will be invoked as a callable. If a
     * class-string is passed instead of an object, the container will attempt to
     * resolve the object from itself before calling the method on the instance.
     *
     * @template T
     * @param \Closure():T|object|class-string $object
     * @phpstan-return ($object is \Closure ? T : mixed)
     */
    public function call(
        object|string $object,
        string $method = '__invoke',
        OverrideCollection|null $overrides = null,
    ): mixed;
}
