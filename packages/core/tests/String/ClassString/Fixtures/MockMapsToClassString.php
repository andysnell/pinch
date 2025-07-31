<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\ClassString\Fixtures;

use PhoneBurner\Pinch\String\ClassString\ClassString;
use PhoneBurner\Pinch\String\ClassString\MapsToClassString;

/**
 * @template T of object
 * @implements MapsToClassString<T>
 */
final readonly class MockMapsToClassString implements MapsToClassString
{
    /**
     * @param ClassString<T> $classString
     */
    public function __construct(private ClassString $classString)
    {
    }

    /**
     * @return ClassString<T>
     */
    public function mapsTo(): ClassString
    {
        return $this->classString;
    }
}
