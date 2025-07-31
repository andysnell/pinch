<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Exception\UnreachableCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UnreachableCodeTest extends TestCase
{
    #[Test]
    public function extendsLogicException(): void
    {
        $exception = new UnreachableCode();

        self::assertInstanceOf(\LogicException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    #[Test]
    public function isFinalClass(): void
    {
        $reflection = new \ReflectionClass(UnreachableCode::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function hasContractAttribute(): void
    {
        $reflection = new \ReflectionClass(UnreachableCode::class);
        $attributes = $reflection->getAttributes(Contract::class);

        self::assertCount(1, $attributes);
        self::assertSame(Contract::class, $attributes[0]->getName());
    }

    #[Test]
    public function canBeCreatedWithDefaultMessage(): void
    {
        $exception = new UnreachableCode();

        // Should have empty message by default since no constructor override
        self::assertSame('', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function canBeCreatedWithCustomMessage(): void
    {
        $message = 'This code should never be reached';
        $exception = new UnreachableCode($message);

        self::assertSame($message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function canBeCreatedWithMessageCodeAndPrevious(): void
    {
        $message = 'Unreachable code path executed';
        $code = 999;
        $previous = new \LogicException('Previous logic error');

        $exception = new UnreachableCode($message, $code, $previous);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(UnreachableCode::class);
        $this->expectExceptionMessage('This should never happen');

        throw new UnreachableCode('This should never happen');
    }

    #[Test]
    public function hasPublicApiStability(): void
    {
        // Test that this class is marked as part of the public API
        $reflection = new \ReflectionClass(UnreachableCode::class);
        $contract_attributes = $reflection->getAttributes(Contract::class);

        self::assertNotEmpty($contract_attributes);
    }

    #[Test]
    public function preventInheritance(): void
    {
        $reflection = new \ReflectionClass(UnreachableCode::class);

        // Verify the class is final and cannot be extended
        self::assertTrue($reflection->isFinal());
    }
}
