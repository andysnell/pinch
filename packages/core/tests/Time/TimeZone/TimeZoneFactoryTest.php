<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\TimeZone;

use PhoneBurner\Pinch\Time\TimeZone\TimeZoneCollection;
use PhoneBurner\Pinch\Time\TimeZone\TimeZoneFactory;
use PhoneBurner\Pinch\Time\TimeZone\Tz;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Array\array_wrap;

#[CoversClass(Tz::class)]
#[CoversClass(TimeZoneFactory::class)]
final class TimeZoneFactoryTest extends TestCase
{
    /**
     * @param value-of<Tz>&string $time_zone_name
     */
    #[DataProvider('providesTimeZoneNames')]
    #[Test]
    public function makeReturnsMemoizedTimeZone(string $time_zone_name): void
    {
        $tz = TimeZoneFactory::make($time_zone_name);
        self::assertSame($tz, TimeZoneFactory::make($time_zone_name));
        self::assertSame($time_zone_name, $tz->getName());
    }

    public static function providesTimeZoneNames(): \Generator
    {
        yield from \array_map(array_wrap(...), \array_column(Tz::cases(), 'value'));
    }

    #[Test]
    public function collectReturnsEmptyTimeZoneCollection(): void
    {
        $collection = TimeZoneFactory::collect();
        self::assertEmpty($collection);
        self::assertSame($collection, TimeZoneFactory::collect());
        self::assertEquals($collection, TimeZoneCollection::make());
    }

    #[Test]
    public function collectReturnsMemoizedTimeZoneCollection(): void
    {
        $collection = TimeZoneFactory::collect(
            Tz::NewYork,
            Tz::Chicago,
            Tz::Denver,
            Tz::LosAngeles,
        );

        self::assertCount(4, $collection);

        self::assertSame($collection, TimeZoneFactory::collect(
            Tz::NewYork,
            Tz::Chicago,
            Tz::Denver,
            Tz::LosAngeles,
        ));

        self::assertSame($collection, TimeZoneFactory::collect(
            new \DateTimeZone(Tz::NewYork->value),
            new \DateTimeZone(Tz::Chicago->value),
            new \DateTimeZone(Tz::Denver->value),
            new \DateTimeZone(Tz::LosAngeles->value),
        ));
    }

    #[Test]
    #[DataProvider('providesTryFromInputs')]
    public function tryFromHandlesDifferentInputTypes(mixed $input, bool $shouldSucceed): void
    {
        $result = TimeZoneFactory::tryFrom($input);

        if ($shouldSucceed) {
            self::assertInstanceOf(\DateTimeZone::class, $result);
        } else {
            self::assertNull($result);
        }
    }

    public static function providesTryFromInputs(): \Generator
    {
        yield 'valid DateTimeZone' => [new \DateTimeZone('UTC'), true];
        yield 'null input' => [null, false];
        yield 'valid Tz enum' => [Tz::Utc, true];
        yield 'valid string' => ['America/New_York', true];
        yield 'invalid timezone name' => [TimeZoneFactory::INVALID_TIME_ZONE_NAME, false];
        yield 'completely invalid string' => ['Invalid/Invalid', false];
        yield 'numeric input' => [123, false];
        yield 'array input' => [['UTC'], false];
        yield 'object input' => [new \stdClass(), false];
    }

    #[Test]
    public function collectHandlesMixedInputTypes(): void
    {
        $collection = TimeZoneFactory::collect(
            Tz::Utc,
            'America/New_York',
            new \DateTimeZone('Europe/London'),
        );

        self::assertCount(3, $collection);
        self::assertInstanceOf(TimeZoneCollection::class, $collection);
    }
}
