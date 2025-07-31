<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Serialization;

use PhoneBurner\Pinch\String\Serialization\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Serializer::class)]
final class SerializerTest extends TestCase
{
    #[Test]
    #[DataProvider('providesSerializerCases')]
    public function serializerEnumCasesExist(Serializer $serializer, string $expectedName): void
    {
        self::assertSame($expectedName, $serializer->name);
    }

    #[Test]
    public function serializerCanBeCompared(): void
    {
        self::assertSame(Serializer::Igbinary, Serializer::Igbinary);
        self::assertNotSame(Serializer::Igbinary, Serializer::Php);
    }

    #[Test]
    public function serializerUsesWithUnitEnumInstanceStaticMethodTrait(): void
    {
        // Test that the trait method is available
        self::assertTrue(\method_exists(Serializer::class, 'instance'));
    }

    #[Test]
    public function serializerInstanceMethodWorks(): void
    {
        // Test the trait functionality
        $instance1 = Serializer::instance('Igbinary');
        $instance2 = Serializer::instance('igbinary'); // case insensitive

        self::assertSame(Serializer::Igbinary, $instance1);
        self::assertSame(Serializer::Igbinary, $instance2);
    }

    #[Test]
    public function serializerCastMethodWorks(): void
    {
        self::assertSame(Serializer::Php, Serializer::cast('Php'));
        self::assertSame(Serializer::Php, Serializer::cast('php'));
        self::assertSame(Serializer::Igbinary, Serializer::cast(Serializer::Igbinary));
        self::assertNull(Serializer::cast('NonExistent'));
    }

    #[Test]
    public function serializerInstanceMethodThrowsOnInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Serializer::instance('NonExistent');
    }

    public static function providesSerializerCases(): \Generator
    {
        yield 'Igbinary case' => [Serializer::Igbinary, 'Igbinary'];
        yield 'Php case' => [Serializer::Php, 'Php'];
    }
}
