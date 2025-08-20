<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class NotResolvable extends \LogicException implements ContainerExceptionInterface
{
    public static function explicit(string $class): self
    {
        return new self($class . ' must be set explicitly at initialization');
    }

    public static function forbidden(string $class): self
    {
        return new self($class . ' may not be resolved directly from the container');
    }

    public static function internal(string $class): self
    {
        return new self($class . ' is an internal class and should not be resolved directly');
    }
}
