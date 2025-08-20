<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Container\Exception;

use PhoneBurner\Pinch\Container\Exception\NotResolvable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotResolvableTest extends TestCase
{
    #[Test]
    public function explicitExceptionMessageContainsClassName(): void
    {
        $class = 'Some\\Class\\Name';
        self::assertSame(
            $class . ' must be set explicitly at initialization',
            NotResolvable::explicit($class)->getMessage(),
        );
    }

    #[Test]
    public function forbiddenExceptionMessageContainsClassName(): void
    {
        $class = 'Some\\Class\\Name';
        self::assertSame(
            $class . ' may not be resolved directly from the container',
            NotResolvable::forbidden($class)->getMessage(),
        );
    }

    #[Test]
    public function internalExceptionMessageContainsClassName(): void
    {
        $class = 'Some\\Class\\Name';
        self::assertSame(
            $class . ' is an internal class and should not be resolved directly',
            NotResolvable::internal($class)->getMessage(),
        );
    }
}
