<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\NotInstantiable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotInstantiableTest extends TestCase
{
    #[Test]
    #[DataProvider('classNameProvider')]
    public function createsCorrectMessage(string $class_name, string $expected_message): void
    {
        $exception = new NotInstantiable($class_name);

        self::assertSame($expected_message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function messageFormattingWithDifferentTypes(): void
    {
        // Test with fully qualified class name
        $exception = new NotInstantiable('PhoneBurner\\Pinch\\Utility\\StaticClass');
        self::assertSame('Class PhoneBurner\\Pinch\\Utility\\StaticClass is not instantiable', $exception->getMessage());

        // Test with simple class name
        $exception = new NotInstantiable('SimpleClass');
        self::assertSame('Class SimpleClass is not instantiable', $exception->getMessage());

        // Test with empty string
        $exception = new NotInstantiable('');
        self::assertSame('Class  is not instantiable', $exception->getMessage());
    }

    public static function classNameProvider(): \Iterator
    {
        yield 'simple class' => ['TestClass', 'Class TestClass is not instantiable'];
        yield 'namespaced class' => ['App\\Utils\\Helper', 'Class App\\Utils\\Helper is not instantiable'];
        yield 'global class' => ['\\stdClass', 'Class \\stdClass is not instantiable'];
        yield 'class with numbers' => ['Class123', 'Class Class123 is not instantiable'];
        yield 'empty string' => ['', 'Class  is not instantiable'];
    }
}
