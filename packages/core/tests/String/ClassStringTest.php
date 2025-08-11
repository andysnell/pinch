<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String;

use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringProhibitsSerialization;
use PhoneBurner\Pinch\String\ClassString\ClassString;
use PhoneBurner\Pinch\String\ClassString\ClassStringType;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClassStringTest extends TestCase
{
    #[Test]
    public function happyPathTestEnum(): void
    {
        $sut = new ClassString(Encoding::class);
        self::assertSame(Encoding::class, (string)$sut);
        self::assertSame(Encoding::class, $sut->value);
        self::assertSame(ClassStringType::Enum, $sut->type);
        self::assertTrue($sut->is(Encoding::Base64));
        self::assertTrue($sut->is(Encoding::class));
        self::assertFalse($sut->is(ClassStringType::class));
        self::assertFalse($sut->is(ClassStringType::Enum));
        self::assertSame(Encoding::class, $sut->reflect()->getName());
        self::assertEquals($sut, \unserialize(\serialize($sut)));
    }

    #[Test]
    public function happyPathTestInterface(): void
    {
        $sut = new ClassString(\Stringable::class);
        self::assertSame(\Stringable::class, (string)$sut);
        self::assertSame(\Stringable::class, $sut->value);
        self::assertSame(ClassStringType::Interface, $sut->type);
        self::assertTrue($sut->is(\Stringable::class));
        self::assertTrue($sut->is(\Stringable::class));
        self::assertFalse($sut->is(new TimeInterval(hours: 1)));
        self::assertFalse($sut->is(TimeInterval::class));
        self::assertSame(\Stringable::class, $sut->reflect()->getName());
        self::assertEquals($sut, \unserialize(\serialize($sut)));
    }

    #[Test]
    public function happyPathTestClass(): void
    {
        $sut = new ClassString(TimeInterval::class);
        self::assertSame(TimeInterval::class, (string)$sut);
        self::assertSame(TimeInterval::class, $sut->value);
        self::assertSame(ClassStringType::Object, $sut->type);
        self::assertTrue($sut->is(TimeInterval::class));
        self::assertTrue($sut->is(\DateInterval::class));
        self::assertTrue($sut->is(\Stringable::class));
        self::assertTrue($sut->is(new TimeInterval(hours: 1)));
        self::assertFalse($sut->is(ClassStringType::class));
        self::assertFalse($sut->is(ClassStringType::Enum));
        self::assertSame(TimeInterval::class, $sut->reflect()->getName());
        self::assertEquals($sut, \unserialize(\serialize($sut)));

        self::assertEquals($sut, ClassString::match(TimeInterval::class, TimeInterval::class));
        self::assertEquals($sut, ClassString::match(TimeInterval::class, \DateInterval::class));
        self::assertEquals($sut, ClassString::match(TimeInterval::class, \Stringable::class));
    }

    #[Test]
    public function sadPathMatch(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(\sprintf("Class '%s' does not match type '%s'", \Stringable::class, TimeInterval::class));
        ClassString::match(\Stringable::class, TimeInterval::class);
    }

    #[Test]
    public function sadPathString(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Class Foo does not exist");
        new ClassString('Foo');
    }

    #[Test]
    public function sadPathTrait(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Traits are not supported");
        new ClassString(BinaryStringProhibitsSerialization::class);
    }
}
