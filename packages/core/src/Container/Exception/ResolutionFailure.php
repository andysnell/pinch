<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class ResolutionFailure extends \LogicException implements ContainerExceptionInterface
{
}
