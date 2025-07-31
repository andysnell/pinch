<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\ClassString;

use PhoneBurner\Pinch\String\ClassString\ClassStringType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassStringType::class)]
final class ClassStringTypeTest extends TestCase
{
    #[Test]
    #[DataProvider('providesClassStringTypes')]
    public function classStringTypeEnumCasesExist(ClassStringType $type, string $expectedName): void
    {
        self::assertSame($expectedName, $type->name);
    }

    #[Test]
    public function classStringTypeCanBeCompared(): void
    {
        self::assertSame(ClassStringType::Object, ClassStringType::Object);
        self::assertNotSame(ClassStringType::Object, ClassStringType::Interface);
    }

    public static function providesClassStringTypes(): \Generator
    {
        yield 'Object case' => [ClassStringType::Object, 'Object'];
        yield 'Interface case' => [ClassStringType::Interface, 'Interface'];
        yield 'Trait case' => [ClassStringType::Trait, 'Trait'];
        yield 'Enum case' => [ClassStringType::Enum, 'Enum'];
    }
}
