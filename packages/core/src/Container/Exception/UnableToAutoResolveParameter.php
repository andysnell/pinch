<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class UnableToAutoResolveParameter extends \LogicException implements ContainerExceptionInterface
{
    public function __construct(\ReflectionParameter $parameter)
    {
        parent::__construct(\sprintf(
            "Unable to resolve value for parameter \$%s for class %s",
            $parameter->getName(),
            $parameter->getDeclaringClass()?->getName(),
        ));
    }
}
