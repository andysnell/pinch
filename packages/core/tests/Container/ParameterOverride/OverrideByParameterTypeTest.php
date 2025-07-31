<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Container\ParameterOverride;

use PhoneBurner\Pinch\Container\ParameterOverride\OverrideByParameterType;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverrideByParameterTypeTest extends TestCase
{
    #[Test]
    public function happyPath(): void
    {
        $override = new OverrideByParameterType('SomeClassName', 'bar');
        self::assertSame('SomeClassName', $override->identifier());
        self::assertSame('bar', $override->value());
        self::assertSame(OverrideType::Hint, $override->type());

        $override = new OverrideByParameterType('SomeOtherClassName');
        self::assertSame('SomeOtherClassName', $override->identifier());
        self::assertNull($override->value());
        self::assertSame(OverrideType::Hint, $override->type());
    }

    #[Test]
    public function identifierMustBeNonempty(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new OverrideByParameterType('', 'bar');
    }
}
