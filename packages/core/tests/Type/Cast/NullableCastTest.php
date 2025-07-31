<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Type\Cast;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Tests\Fixtures\IntBackedEnum;
use PhoneBurner\Pinch\Tests\Fixtures\StoplightState;
use PhoneBurner\Pinch\Time\Standards\AnsiSql;
use PhoneBurner\Pinch\Type\Cast\NullableCast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableCastTest extends TestCase
{
    #[DataProvider('providesIntegerTestCases')]
    #[Test]
    public function integerReturnsExpectedValue(mixed $input, int|null $expected): void
    {
        self::assertSame($expected, NullableCast::integer($input));
    }

    public static function providesIntegerTestCases(): \Generator
    {
        yield [0, 0];
        yield [1, 1];
        yield [-1, -1];
        yield [1.4433, 1];
        yield [\PHP_INT_MAX, \PHP_INT_MAX];
        yield ['432', 432];
        yield ["hello, world", 0];
        yield ['0', 0];
        yield [true, 1];
        yield [false, 0];
        yield [null, null];
    }

    #[DataProvider('providesFloatTestCases')]
    #[Test]
    public function floatReturnsExpectedValue(mixed $input, float|null $expected): void
    {
        self::assertSame($expected, NullableCast::float($input));
    }

    public static function providesFloatTestCases(): \Generator
    {
        yield [0, 0.0];
        yield [1, 1.0];
        yield [-1, -1.0];
        yield [1.4433, 1.4433];
        yield [\PHP_INT_MAX, (float)\PHP_INT_MAX];
        yield ['432', 432.0];
        yield ["hello, world", 0.0];
        yield ['0', 0.0];
        yield [true, 1.0];
        yield [false, 0.0];
        yield [null, null];
        yield [IntBackedEnum::Bar, 2.0];
        yield [StoplightState::Red, 0.0];
    }

    #[DataProvider('providesStringTestCases')]
    #[Test]
    public function stringReturnsExpectedValue(mixed $input, string|null $expected): void
    {
        self::assertSame($expected, NullableCast::string($input));
    }

    public static function providesStringTestCases(): \Generator
    {
        yield [0, '0'];
        yield [1, '1'];
        yield [-1, '-1'];
        yield [1.4433, '1.4433'];
        yield [\PHP_INT_MAX, (string)\PHP_INT_MAX];
        yield ['432', '432'];
        yield ["hello, world", "hello, world"];
        yield ['0', '0'];
        yield [true, '1'];
        yield [false, ''];
        yield [null, null];
        yield [IntBackedEnum::Bar, '2'];
        yield [StoplightState::Red, 'red'];
    }

    #[DataProvider('providesBooleanTestCases')]
    #[Test]
    public function booleanReturnsExpectedValue(mixed $input, bool|null $expected): void
    {
        self::assertSame($expected, NullableCast::boolean($input));
    }

    public static function providesBooleanTestCases(): \Generator
    {
        yield [0, false];
        yield [1, true];
        yield [-1, true];
        yield [1.4433, true];
        yield [\PHP_INT_MAX, true];
        yield ['432', true];
        yield ["hello, world", true];
        yield ['0', false];
        yield [true, true];
        yield [false, false];
        yield [null, null];
        yield [IntBackedEnum::Bar, true];
        yield [StoplightState::Red, true];
    }

    #[DataProvider('providesDatetimeTestCases')]
    #[Test]
    public function datetimeReturnsExpectedValue(mixed $input, CarbonImmutable|null $expected): void
    {
        $datetime = NullableCast::datetime($input);
        if ($expected instanceof CarbonImmutable) {
            self::assertInstanceOf(CarbonImmutable::class, $datetime);
            self::assertEquals($expected->getTimestamp(), $datetime->getTimestamp());
        } else {
            self::assertNull($datetime);
        }
    }

    public static function providesDatetimeTestCases(): \Generator
    {
        $datetime = new CarbonImmutable('2025-02-03 19:19:31');

        yield [null, null];
        yield ['', null];
        yield ['invalid time string', null];
        yield [0, CarbonImmutable::createFromTimestamp(0)];
        yield [AnsiSql::NULL_DATETIME, null];
        yield ['2021-01-01 00:00:00', new CarbonImmutable('2021-01-01 00:00:00')];
        yield ['2021-01-01', new CarbonImmutable('2021-01-01')];
        yield ['19:19:31', new CarbonImmutable('19:19:31')];
        yield [new CarbonImmutable('2025-02-03 19:19:31'), $datetime];
        yield [new CarbonImmutable('2025-02-03T14:19:31-0500'), $datetime];
        yield [new \DateTimeImmutable('2025-02-03 19:19:31'), $datetime];
        /** @phpstan-ignore disallowed.class (this is a test) */
        yield [new \DateTime('2025-02-03 19:19:31'), $datetime];
        yield [1738610371, $datetime];
    }

    #[Test]
    public function integerThrowsTypeErrorForInvalidType(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type for integer cast: array');
        NullableCast::integer([]);
    }

    #[Test]
    public function integerThrowsTypeErrorForObject(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type for integer cast: object');
        NullableCast::integer(new \stdClass());
    }

    #[Test]
    public function integerHandlesBackedEnum(): void
    {
        self::assertSame(2, NullableCast::integer(IntBackedEnum::Bar));
        self::assertSame(0, NullableCast::integer(StoplightState::Red));
    }

    #[Test]
    public function floatThrowsTypeErrorForInvalidType(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type for float cast: array');
        NullableCast::float([]);
    }

    #[Test]
    public function floatThrowsTypeErrorForObject(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type for float cast: object');
        NullableCast::float(new \stdClass());
    }

    #[Test]
    public function stringThrowsTypeErrorForInvalidType(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type for string cast: array');
        NullableCast::string([]);
    }

    #[Test]
    public function stringThrowsTypeErrorForObject(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type for string cast: object');
        NullableCast::string(new \stdClass());
    }

    #[Test]
    public function stringHandlesStringableObject(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };

        self::assertSame('test', NullableCast::string($stringable));
    }
}
