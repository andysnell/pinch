<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotInstantiable extends NotFound
{
    public function __construct(string $class)
    {
        parent::__construct(\sprintf('cannot autowire non-instantiable %s', $class));
    }

    public static function id(string $id): self
    {
        return new self(\sprintf('no entry for id%s', $id ? ' "$id"' : ''));
    }
}
