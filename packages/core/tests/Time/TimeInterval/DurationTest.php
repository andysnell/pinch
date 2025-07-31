<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\TimeInterval;

use PhoneBurner\Pinch\Time\TimeInterval\Duration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\Pinch\Time\DAYS_IN_WEEK;

final class DurationTest extends TestCase
{
    #[Test]
    #[DataProvider('iso8601DurationProvider')]
    public function parseHappyPath(string $duration, array $expected, string|null $formatted = null): void
    {
        // test constructor
        $sut = new Duration(...$expected);
        foreach (Duration::UNITS as $unit) {
            self::assertSame($expected[$unit], $sut->$unit);
        }
        self::assertSame($formatted ?? $duration, (string)$sut);

        // test upper case duration string
        $sut = Duration::parse($duration);
        foreach (Duration::UNITS as $unit) {
            self::assertSame($expected[$unit], $sut?->$unit);
        }

        // test lower case duration string
        $sut = Duration::parse(\strtolower($duration));
        foreach (Duration::UNITS as $unit) {
            self::assertSame($expected[$unit], $sut?->$unit);
        }

        // DateInterval cannot be constructed with duration strings with fractional seconds
        if ($expected['microseconds'] !== 0) {
            return;
        }

        $sut = Duration::make(new \DateInterval($duration));

        // DateInterval converts weeks to days;
        if ($expected['weeks'] !== 0) {
            $expected['days'] = $expected['weeks'] * DAYS_IN_WEEK;
            $expected['weeks'] = 0;
        }

        foreach (Duration::UNITS as $unit) {
            self::assertSame($expected[$unit], $sut->$unit);
        }
    }

    public static function iso8601DurationProvider(): \Generator
    {
        yield ['P3W', [
            'weeks' => 3,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P1W', [
            'weeks' => 1,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P52W', [
            'weeks' => 52,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P104W', [
            'weeks' => 104,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P0W', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ], Duration::EMPTY_DURATION];

        yield ['P1Y', [
            'weeks' => 0,
            'years' => 1,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P2M', [
            'weeks' => 0,
            'years' => 0,
            'months' => 2,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P10D', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 10,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P1Y2M', [
            'weeks' => 0,
            'years' => 1,
            'months' => 2,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P3Y5D', [
            'weeks' => 0,
            'years' => 3,
            'months' => 0,
            'days' => 5,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['PT5H', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 5,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['PT30M', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 30,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['PT45S', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 45,
            'microseconds' => 0,
        ]];

        yield ['PT1H15M', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 1,
            'minutes' => 15,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['PT20.5S', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 20,
            'microseconds' => 500000,
        ]];

        yield ['P1Y2M3DT4H5M6S', [
            'weeks' => 0,
            'years' => 1,
            'months' => 2,
            'days' => 3,
            'hours' => 4,
            'minutes' => 5,
            'seconds' => 6,
            'microseconds' => 0,
        ]];

        yield ['P2DT3H', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 2,
            'hours' => 3,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ]];

        yield ['P3Y6M4DT12H30M5.75S', [
            'weeks' => 0,
            'years' => 3,
            'months' => 6,
            'days' => 4,
            'hours' => 12,
            'minutes' => 30,
            'seconds' => 5,
            'microseconds' => 750000,
        ]];

        yield ['P00Y00M00DT00H00M00S', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ], Duration::EMPTY_DURATION];

        yield ['P5DT0H0M0S', [
            'weeks' => 0,
            'years' => 0,
            'months' => 0,
            'days' => 5,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'microseconds' => 0,
        ], 'P5D'];

        yield ['P7Y11M29DT23H59M59.999999S', [
            'weeks' => 0,
            'years' => 7,
            'months' => 11,
            'days' => 29,
            'hours' => 23,
            'minutes' => 59,
            'seconds' => 59,
            'microseconds' => 999999,
        ]];
    }

    #[Test]
    public function makeCreatesDurationFromDateInterval(): void
    {
        $interval = new \DateInterval('P1Y2M3DT4H5M6S');
        $interval->f = 0.123456; // Add fractional seconds

        $duration = Duration::make($interval);

        self::assertSame(1, $duration->years);
        self::assertSame(2, $duration->months);
        self::assertSame(3, $duration->days);
        self::assertSame(4, $duration->hours);
        self::assertSame(5, $duration->minutes);
        self::assertSame(6, $duration->seconds);
        self::assertSame(123456, $duration->microseconds);
    }

    #[Test]
    public function instanceCreatesFromValidInput(): void
    {
        $duration1 = Duration::instance('P1Y2M3D');
        self::assertSame(1, $duration1->years);
        self::assertSame(2, $duration1->months);
        self::assertSame(3, $duration1->days);

        $existing = new Duration(1, 2, 0, 3);
        $duration2 = Duration::instance($existing);
        self::assertSame($existing, $duration2);

        $interval = new \DateInterval('PT1H');
        $duration3 = Duration::instance($interval);
        self::assertSame(1, $duration3->hours);
    }

    #[Test]
    public function instanceThrowsForInvalidInput(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid Duration');

        Duration::instance('invalid-duration-string');
    }

    #[Test]
    #[DataProvider('providesParseEdgeCases')]
    public function parseHandlesEdgeCases(
        \DateInterval|\Stringable|Duration|string $input,
        Duration|null $expected,
    ): void {
        $result = Duration::parse($input);

        if ($expected === null) {
            self::assertNull($result);
        } else {
            self::assertEquals($expected, $result);
        }
    }

    public static function providesParseEdgeCases(): \Generator
    {
        // Test with Duration instance (should return same instance)
        $existingDuration = new Duration(1, 2, 0, 3);
        yield 'existing Duration instance' => [$existingDuration, $existingDuration];

        // Test with DateInterval
        $interval = new \DateInterval('PT2H30M');
        yield 'DateInterval instance' => [$interval, new Duration(0, 0, 0, 0, 2, 30, 0, 0)];

        // Test invalid strings that should return null
        yield 'invalid duration string' => ['invalid', null];
        yield 'empty string' => ['', null];
        yield 'partial invalid pattern' => ['P1X', null];
        yield 'completely malformed' => ['XYZ123', null];

        // Test \Stringable object
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'P1DT2H';
            }
        };
        yield 'Stringable object' => [$stringable, new Duration(0, 0, 0, 1, 2, 0, 0, 0)];

        // Test exception-causing scenarios within the try-catch
        $invalidStringable = new class implements \Stringable {
            public function __toString(): string
            {
                throw new \Exception('Test exception');
            }
        };
        yield 'Stringable that throws exception' => [$invalidStringable, null];
    }

    #[Test]
    #[DataProvider('providesConstructorValidationCases')]
    public function constructorValidatesInputs(
        array $args,
        string $expectedExceptionClass,
        string $expectedMessage,
    ): void {
        /** @var class-string<\Throwable> $expectedExceptionClass */
        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedMessage);

        new Duration(...$args);
    }

    public static function providesConstructorValidationCases(): \Generator
    {
        yield 'negative years' => [
            ['years' => -1],
            \UnexpectedValueException::class,
            'years must be greater than or equal to 0',
        ];

        yield 'negative months' => [
            ['months' => -1],
            \UnexpectedValueException::class,
            'months must be greater than or equal to 0',
        ];

        yield 'negative weeks' => [
            ['weeks' => -1],
            \UnexpectedValueException::class,
            'weeks must be greater than or equal to 0',
        ];

        yield 'negative days' => [
            ['days' => -1],
            \UnexpectedValueException::class,
            'days must be greater than or equal to 0',
        ];

        yield 'negative hours' => [
            ['hours' => -1],
            \UnexpectedValueException::class,
            'hours must be greater than or equal to 0',
        ];

        yield 'negative minutes' => [
            ['minutes' => -1],
            \UnexpectedValueException::class,
            'minutes must be greater than or equal to 0',
        ];

        yield 'negative seconds' => [
            ['seconds' => -1],
            \UnexpectedValueException::class,
            'seconds must be greater than or equal to 0',
        ];

        yield 'negative microseconds' => [
            ['microseconds' => -1],
            \UnexpectedValueException::class,
            'microseconds must be greater than or equal to 0',
        ];

        yield 'weeks mixed with other units' => [
            ['weeks' => 1, 'days' => 1],
            \UnexpectedValueException::class,
            'invalid duration: cannot mix weeks and other units',
        ];

        yield 'microseconds overflow' => [
            ['microseconds' => 1_000_000],
            \OverflowException::class,
            'invalid duration: cannot overflow fractional seconds',
        ];
    }
}
