<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class ServiceProviderAlreadyRegistered extends \LogicException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        parent::__construct($class . ' has already been registered with this container instance');
    }
}
