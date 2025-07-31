<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class OverriddenArgumentNotSet extends \LogicException implements ContainerExceptionInterface
{
}
