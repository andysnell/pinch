<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\SerializationProhibited;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SerializationProhibitedTest extends TestCase
{
    #[Test]
    public function hasFixedMessage(): void
    {
        $exception = new SerializationProhibited();

        self::assertInstanceOf(\LogicException::class, $exception);
        self::assertSame('Serialization of Objects with Sensitive Parameters is Prohibited', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
