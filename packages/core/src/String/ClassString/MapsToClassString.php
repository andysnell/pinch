<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\ClassString;

/**
 * @template T of object
 */
interface MapsToClassString
{
    /**
     * @return ClassString<T>
     */
    public function mapsTo(): ClassString;
}
