<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Container\ClassStringObjectContainer;
use PhoneBurner\Pinch\Container\ObjectContainer;

/**
 * Defines a container that can autowire services that are not explicitly
 * registered. This interface makes no guarantee about the mutability of the
 * container or whether it supports invoking methods on objects resolved from
 * it.
 *
 * @template T of object
 * @extends ObjectContainer<T>
 * @extends ClassStringObjectContainer<T>
 */
#[Contract]
interface AutowiringContainer extends ObjectContainer, ClassStringObjectContainer
{
    /**
     * Returns true if:
     *  1) We already have a resolved entry for the $id
     *  2) We have a service factory that can resolve the entry
     *  3) A deferred service provider that can register an entry or service factory
     *
     * If the $strict parameter is false, it will also return true if:
     *  4) The $id string is a valid class-string for a class that we could potentially
     *     autowire, i.e., it is not an interface, trait, or abstract class.
     */
    public function has(\Stringable|string $id, bool $strict = false): bool;
}
