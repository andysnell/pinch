<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class ResolutionFailure extends \LogicException implements ContainerExceptionInterface
{
    public static function withIdNotClassString(string $id): self
    {
        return new self(\sprintf('Service "%s" must be a class string', $id));
    }

    public static function withDeferredServiceNotRegistered(string $id): self
    {
        return new self(\sprintf('Deferred Service "%s" was not registered by its provider', $id));
    }
}
