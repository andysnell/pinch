<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Attribute;

use PhoneBurner\Pinch\Tests\Fixtures\Attributes\MockAttribute;
use PhoneBurner\Pinch\Tests\Fixtures\ClassWithAttributes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Attribute\attr_find;
use function PhoneBurner\Pinch\Attribute\attr_first;

final class AttributeFunctionsTest extends TestCase
{
    #[Test]
    public function findReturnsAttributesOnClass(): void
    {
        $attributes = attr_find(ClassWithAttributes::class, MockAttribute::class);
        self::assertCount(1, $attributes);
        self::assertInstanceOf(MockAttribute::class, $attributes[0]);
        self::assertSame('class', $attributes[0]->name);
    }

    #[Test]
    public function findReturnsAttributesOnReflectionClass(): void
    {
        $reflection = new \ReflectionClass(ClassWithAttributes::class);
        $attributes = attr_find($reflection, MockAttribute::class);
        self::assertCount(1, $attributes);
        self::assertInstanceOf(MockAttribute::class, $attributes[0]);
        self::assertSame('class', $attributes[0]->name);
    }

    #[Test]
    public function findReturnsAttributesOnReflectionMethod(): void
    {
        $reflection = new \ReflectionMethod(ClassWithAttributes::class, 'method');
        $attributes = attr_find($reflection, MockAttribute::class);
        self::assertCount(1, $attributes);
        self::assertInstanceOf(MockAttribute::class, $attributes[0]);
        self::assertSame('method', $attributes[0]->name);
    }

    #[Test]
    public function findReturnsAttributesOnReflectionProperty(): void
    {
        $reflection = new \ReflectionProperty(ClassWithAttributes::class, 'property');
        $attributes = attr_find($reflection, MockAttribute::class);
        self::assertCount(1, $attributes);
        self::assertInstanceOf(MockAttribute::class, $attributes[0]);
        self::assertSame('property', $attributes[0]->name);
    }

    #[Test]
    public function findReturnsAttributesOnObject(): void
    {
        $object = new ClassWithAttributes();
        $attributes = attr_find($object, MockAttribute::class);
        self::assertCount(1, $attributes);
        self::assertInstanceOf(MockAttribute::class, $attributes[0]);
        self::assertSame('class', $attributes[0]->name);
    }

    #[Test]
    public function findReturnsEmptyArrayWhenNoAttributesFound(): void
    {
        $attributes = attr_find(\stdClass::class, MockAttribute::class);
        self::assertCount(0, $attributes);
    }

    #[Test]
    public function firstReturnsFirstAttribute(): void
    {
        $attribute = attr_first(ClassWithAttributes::class, MockAttribute::class);
        self::assertInstanceOf(MockAttribute::class, $attribute);
        self::assertSame('class', $attribute->name);
    }

    #[Test]
    public function firstReturnsNullWhenNoAttributesFound(): void
    {
        $attribute = attr_first(\stdClass::class, MockAttribute::class);
        self::assertNull($attribute);
    }

    #[Test]
    #[DataProvider('invalidReflectorProvider')]
    public function findThrowsExceptionForInvalidReflector(mixed $invalid_reflector): void
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @phpstan-ignore argument.type (intentional defect) */
        attr_find($invalid_reflector);
    }

    public static function invalidReflectorProvider(): \Iterator
    {
        $reflector = new class implements \Reflector {
            public function __toString(): string
            {
                return 'Invalid reflector';
            }

            public static function export(): null
            {
                // TODO: compatible with < PHP 8.0 definitions
                return null;
            }
        };

        yield 'custom reflector' => [$reflector];
    }
}
