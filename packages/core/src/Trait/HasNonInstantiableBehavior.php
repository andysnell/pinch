<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Trait;

use PhoneBurner\Pinch\Exception\NotInstantiable;

trait HasNonInstantiableBehavior
{
    final public function __construct()
    {
        throw new NotInstantiable(self::class);
    }
}
