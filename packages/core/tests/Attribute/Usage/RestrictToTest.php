<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Attribute\Usage;

use PhoneBurner\Pinch\Attribute\Usage\RestrictTo;
use PhoneBurner\Pinch\Tests\Fixtures\ServiceFactoryTestClass;
use PhoneBurner\Pinch\Tests\Fixtures\StaticServiceFactoryTestClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RestrictToTest extends TestCase
{
    #[Test]
    public function happyPath(): void
    {
        $sut = new RestrictTo(ServiceFactoryTestClass::class);
        self::assertSame([ServiceFactoryTestClass::class], $sut->classes);
    }

    #[Test]
    public function happyPathWithMultipleClasses(): void
    {
        $sut = new RestrictTo(ServiceFactoryTestClass::class, StaticServiceFactoryTestClass::class);
        self::assertSame([
            ServiceFactoryTestClass::class,
            StaticServiceFactoryTestClass::class,
        ], $sut->classes);
    }

    #[Test]
    public function sadPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RestrictTo();
    }
}
