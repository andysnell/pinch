<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Encoding;

use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoder;
use PhoneBurner\Pinch\String\Encoding\Rfc4648Encoder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class Rfc4648EncoderTest extends TestCase
{
    #[Test]
    public function interfaceIsImplementedByConstantTimeEncoder(): void
    {
        self::assertInstanceOf(Rfc4648Encoder::class, new ConstantTimeEncoder());
    }

    #[Test]
    public function interfaceIsImplementedByEncoder(): void
    {
        self::assertInstanceOf(Rfc4648Encoder::class, new Encoder());
    }

    #[Test]
    public function interfaceDefinesRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(Rfc4648Encoder::class);

        self::assertTrue($reflection->hasMethod('encode'));
        self::assertTrue($reflection->hasMethod('decode'));
    }

    #[Test]
    public function encodeMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(Rfc4648Encoder::class);
        $method = $reflection->getMethod('encode');

        self::assertTrue($method->isStatic());
        self::assertTrue($method->isPublic());
        $return_type = $method->getReturnType();
        self::assertSame('string', $return_type instanceof \ReflectionNamedType ? $return_type->getName() : null);

        $parameters = $method->getParameters();
        self::assertCount(3, $parameters);
        self::assertSame('encoding', $parameters[0]->getName());
        self::assertSame('value', $parameters[1]->getName());
        self::assertSame('prefix', $parameters[2]->getName());
        self::assertTrue($parameters[2]->isDefaultValueAvailable());
        self::assertFalse($parameters[2]->getDefaultValue());
    }

    #[Test]
    public function decodeMethodHasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(Rfc4648Encoder::class);
        $method = $reflection->getMethod('decode');

        self::assertTrue($method->isStatic());
        self::assertTrue($method->isPublic());
        $return_type = $method->getReturnType();
        self::assertSame('string', $return_type instanceof \ReflectionNamedType ? $return_type->getName() : null);

        $parameters = $method->getParameters();
        self::assertCount(2, $parameters);
        self::assertSame('encoding', $parameters[0]->getName());
        self::assertSame('value', $parameters[1]->getName());
    }
}
