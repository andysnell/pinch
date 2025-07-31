<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\NotImplemented;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotImplementedTest extends TestCase
{
    #[Test]
    public function extendsLogicException(): void
    {
        $exception = new NotImplemented();

        self::assertInstanceOf(\LogicException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    #[Test]
    public function canBeCreatedWithDefaultMessage(): void
    {
        $exception = new NotImplemented();

        // Should have empty message by default since no constructor override
        self::assertSame('', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function canBeCreatedWithCustomMessage(): void
    {
        $message = 'This method is not yet implemented';
        $exception = new NotImplemented($message);

        self::assertSame($message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function canBeCreatedWithMessageCodeAndPrevious(): void
    {
        $message = 'Feature not implemented';
        $code = 42;
        $previous = new \RuntimeException('Previous exception');

        $exception = new NotImplemented($message, $code, $previous);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(NotImplemented::class);
        $this->expectExceptionMessage('Not yet implemented');

        throw new NotImplemented('Not yet implemented');
    }
}
