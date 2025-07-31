<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\SerializationFailure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SerializationFailureTest extends TestCase
{
    #[Test]
    public function extendsRuntimeException(): void
    {
        $exception = new SerializationFailure();

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    #[Test]
    public function canBeCreatedWithDefaultMessage(): void
    {
        $exception = new SerializationFailure();

        // Should have empty message by default since no constructor override
        self::assertSame('', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function canBeCreatedWithCustomMessage(): void
    {
        $message = 'Failed to serialize object';
        $exception = new SerializationFailure($message);

        self::assertSame($message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function canBeCreatedWithMessageCodeAndPrevious(): void
    {
        $message = 'Serialization error occurred';
        $code = 500;
        $previous = new \RuntimeException('Underlying error');

        $exception = new SerializationFailure($message, $code, $previous);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('Unable to serialize data');

        throw new SerializationFailure('Unable to serialize data');
    }

    #[Test]
    public function canWrapOtherExceptions(): void
    {
        $previous = new \JsonException('Malformed JSON');
        $exception = new SerializationFailure('JSON serialization failed', 0, $previous);

        self::assertSame('JSON serialization failed', $exception->getMessage());
        self::assertInstanceOf(\JsonException::class, $exception->getPrevious());
        self::assertSame('Malformed JSON', $exception->getPrevious()->getMessage());
    }
}
