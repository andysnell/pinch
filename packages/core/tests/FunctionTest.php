<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Tests\Fixtures\FinalClassWithPublicProperty;
use PhoneBurner\Pinch\Time\TimeZone\Tz;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\compose;
use function PhoneBurner\Pinch\func_fwd;
use function PhoneBurner\Pinch\ghost;
use function PhoneBurner\Pinch\noop;
use function PhoneBurner\Pinch\not;
use function PhoneBurner\Pinch\nullify;
use function PhoneBurner\Pinch\once;
use function PhoneBurner\Pinch\proxy;
use function PhoneBurner\Pinch\retry;
use function PhoneBurner\Pinch\tap;
use function PhoneBurner\Pinch\value;

final class FunctionTest extends TestCase
{
    #[Test]
    public function proxyInitializesObjectWhenAccessed(): void
    {
        $test_class = new FinalClassWithPublicProperty();
        $test_class->property = 'initialized';

        $factory = static fn(FinalClassWithPublicProperty $object): FinalClassWithPublicProperty => $test_class;
        $proxy = proxy($factory);

        self::assertInstanceOf(FinalClassWithPublicProperty::class, $proxy);
        self::assertNotSame($test_class, $proxy);
        self::assertSame('initialized', $proxy->property);
    }

    #[Test]
    public function ghostCreatesLazyGhostObject(): void
    {
        $initializer = static function (FinalClassWithPublicProperty $object): void {
            $object->property = 'value';
        };

        $ghost = ghost($initializer);

        self::assertInstanceOf(FinalClassWithPublicProperty::class, $ghost);
        self::assertSame('value', $ghost->property);
    }

    #[Test]
    #[DataProvider('nullIfFalseDataProvider')]
    public function nullIfFalseReturnsExpectedValue(mixed $input, mixed $expected): void
    {
        self::assertSame($expected, nullify($input));
    }

    public static function nullIfFalseDataProvider(): \Generator
    {
        $object = new \stdClass();
        yield 'false returns null' => [false, null];
        yield 'null returns null' => [null, null];
        yield 'string returns string' => ['value', 'value'];
        yield 'integer returns integer' => [123, 123];
        yield 'empty array returns empty array' => [[], []];
        yield 'zero returns zero' => [0, 0];
        yield 'empty string returns empty string' => ['', ''];
        yield 'zero float returns zero float' => [0.0, 0.0];
        yield 'object returns same object' => [$object, $object];
    }

    /**
     * @param array<mixed> $args
     */
    #[DataProvider('providesCallableValuesWithArgs')]
    #[Test]
    public function valueCallsCallableValues(callable $test, array $args, mixed $expected): void
    {
        self::assertSame($expected, value($test, ...$args));
    }

    #[DataProvider('providesCallableValuesWithoutArgs')]
    #[Test]
    public function valueCallsCallableValuesWithoutArgs(callable $test, mixed $expected): void
    {
        self::assertSame($expected, value($test));
    }

    #[Test]
    public function fwdReturnsCallableThatCallsMethodOnObjectWithoutArgs(): void
    {
        $epoch = CarbonImmutable::createFromTimestamp(0);
        $dates = \array_map(static fn(int $i): CarbonImmutable => $epoch->addMinutes($i), [0, 1, 2, 3]);

        self::assertSame([0, 60, 120, 180,], \array_map(func_fwd('getTimestamp'), $dates));
    }

    #[Test]
    public function fwdReturnsCallableThatCallsMethodOnObjectWithSingleArgs(): void
    {
        $epoch = CarbonImmutable::createFromTimestamp(1, Tz::Utc->value);
        $dates = \array_map(static fn(int $i): CarbonImmutable => $epoch->addDays($i), [0, 1, 2, 3]);

        self::assertSame([
            '1970-01-01',
            '1970-01-02',
            '1970-01-03',
            '1970-01-04',
        ], \array_map(func_fwd('format', 'Y-m-d'), $dates));
    }

    #[Test]
    public function fwdReturnsCallableThatCallsMethodOnObjectWithMultipleArgs(): void
    {
        $arr = \array_map(static fn(int $i): object => self::makeMockObject($i), [0, 1, 2, 3]);

        self::assertSame([
            0,
            2 * 3 * 5,
            2 * 2 * 3 * 5,
            3 * 2 * 3 * 5,
        ], \array_map(func_fwd('foo', 2, 3, 5), $arr));
    }

    #[Test]
    public function noopReturnsFunctionThatDoesNothing(): void
    {
        $func = noop();
        self::assertNull($func());
    }

    private static function makeMockObject(int $i): object
    {
        return new readonly class ($i) {
            public function __construct(private int $i)
            {
            }

            public function foo(int $j, int $k, int $l): int
            {
                return $this->i * $j * $k * $l;
            }
        };
    }

    public static function providesCallableValuesWithoutArgs(): \Generator
    {
        yield [static fn(): int => 123, 123];

        $class = new class () {
            public function __invoke(): int
            {
                return 999;
            }
        };

        yield [$class, 999];
    }

    public static function providesCallableValuesWithArgs(): \Generator
    {
        yield [static fn(): int => 123, [], 123];
        yield [static fn(): int => 123, [22, 10], 123];
        yield [static fn($i): int|float => 123 + $i, [22, 10], 145];
        yield [static fn($i, $j): int|float => 123 + $i + $j, [22, 10], 155];

        $class = new class () {
            public function __invoke(int $i = 0, int $j = 0): int
            {
                return 123 + $i + $j;
            }
        };

        yield [$class, [], 123];
        yield [$class, [22], 145];
        yield [$class, [22, 10], 155];

        yield ['trim', ['  Hello, World  '], 'Hello, World'];
    }

    /**
     * @param array<mixed> $args
     */
    #[DataProvider('providesNonCallableValues')]
    #[Test]
    public function valuePassesThroughNonCallableValues(mixed $test, array $args): void
    {
        self::assertSame($test, value($test, ...$args));
    }

    public static function providesNonCallableValues(): \Generator
    {
        $values = [
            true,
            false,
            null,
            'string',
            12343,
            123.33,
            new \stdClass(),
        ];

        foreach ($values as $value) {
            yield [$value, []];
        }

        foreach ($values as $value) {
            yield [$value, [23, 23, 23]];
        }
    }

    #[Test]
    public function retrySucceedsOnFirstAttempt(): void
    {
        $call_count = 0;
        $callback = static function () use (&$call_count): string {
            ++$call_count;
            return 'success';
        };

        $result = retry($callback, 3, 100);

        self::assertSame('success', $result);
        self::assertSame(1, $call_count);
    }

    #[Test]
    public function retrySucceedsAfterRetries(): void
    {
        $call_count = 0;
        $callback = static function () use (&$call_count): string {
            ++$call_count;
            if ($call_count < 3) {
                throw new \RuntimeException('Not yet');
            }
            return 'success';
        };

        $result = retry($callback, 5, 10);

        self::assertSame('success', $result);
        self::assertSame(3, $call_count);
    }

    #[Test]
    public function retryThrowsExceptionWhenAllAttemptsFail(): void
    {
        $call_count = 0;
        $callback = static function () use (&$call_count): never {
            ++$call_count;
            throw new \RuntimeException('Always fails');
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Always fails');

        try {
            retry($callback, 3, 10);
        } finally {
            self::assertSame(3, $call_count);
        }
    }

    #[Test]
    public function retryValidatesMaxAttempts(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max_attempts must be greater than 0');

        retry(static fn(): string => 'test', 0);
    }

    #[Test]
    public function retryValidatesNegativeMaxAttempts(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('max_attempts must be greater than 0');

        retry(static fn(): string => 'test', -1);
    }

    #[Test]
    public function retryValidatesDelayMicroseconds(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('delay_microseconds must be greater than or equal to 0');

        retry(static fn(): string => 'test', 1, -1);
    }

    #[Test]
    public function retryAllowsZeroDelay(): void
    {
        $result = retry(static fn(): string => 'success', 1, 0);
        self::assertSame('success', $result);
    }

    #[Test]
    public function notNegatesCallableResult(): void
    {
        $is_positive = static fn(int $n): bool => $n > 0;
        $is_not_positive = not($is_positive);

        self::assertTrue($is_not_positive(-5));
        self::assertTrue($is_not_positive(0));
        self::assertFalse($is_not_positive(5));
    }

    #[Test]
    public function notWorksWithMultipleArgs(): void
    {
        $equals = static fn(int $a, int $b): bool => $a === $b;
        $not_equals = not($equals);

        self::assertTrue($not_equals(1, 2));
        self::assertFalse($not_equals(5, 5));
    }

    #[Test]
    public function composeCreatesPipelineOfFunctions(): void
    {
        $trim = static fn(string $s): string => \trim($s);
        $upper = static fn(string $s): string => \strtoupper($s);
        $exclaim = static fn(string $s): string => $s . '!';

        $shout = compose($trim, $upper, $exclaim);

        self::assertSame('HELLO!', $shout('  hello  '));
    }

    #[Test]
    public function composeWorksWithEmptyFunctionList(): void
    {
        $identity = compose();
        self::assertSame('test', $identity('test'));
        self::assertSame(42, $identity(42));
    }

    #[Test]
    public function composeWorksWithSingleFunction(): void
    {
        $double = static fn(int $n): int => $n * 2;
        $composed = compose($double);

        self::assertSame(10, $composed(5));
    }

    #[Test]
    public function composeWorksWithComplexPipeline(): void
    {
        $add_one = static fn(int $n): int => $n + 1;
        $multiply_by_two = static fn(int $n): int => $n * 2;
        $to_string = static fn(int $n): string => (string)$n;

        $pipeline = compose($add_one, $multiply_by_two, $to_string);

        self::assertSame('6', $pipeline(2)); // (2 + 1) * 2 = "6"
    }

    #[Test]
    public function tapExecutesSideEffectAndReturnsOriginalValue(): void
    {
        $side_effect_value = null;
        $callback = static function (string $value) use (&$side_effect_value): void {
            $side_effect_value = $value . ' modified';
        };

        $result = tap('original', $callback);

        self::assertSame('original', $result);
        self::assertSame('original modified', $side_effect_value);
    }

    #[Test]
    public function tapWorksWithObjects(): void
    {
        $obj = new \stdClass();
        $obj->value = 'initial';

        $result = tap($obj, static function (\stdClass $o): void {
            $o->value = 'modified';
        });

        self::assertSame($obj, $result);
        self::assertSame('modified', $obj->value);
    }

    #[Test]
    public function onceExecutesFunctionOnlyOnce(): void
    {
        $call_count = 0;
        $fn = static function () use (&$call_count): int {
            ++$call_count;
            return 42;
        };

        $once_fn = once($fn);

        self::assertSame(42, $once_fn());
        self::assertSame(42, $once_fn());
        self::assertSame(42, $once_fn());
        self::assertSame(1, $call_count);
    }

    #[Test]
    public function onceCachesReturnValue(): void
    {
        $fn = static function (): int {
            return \random_int(1, 1000000);
        };

        $once_fn = once($fn);
        $first_result = $once_fn();
        $second_result = $once_fn();

        self::assertSame($first_result, $second_result);
    }

    #[Test]
    public function onceWorksWithNullReturnValue(): void
    {
        $call_count = 0;
        $fn = static function () use (&$call_count): null {
            ++$call_count;
            return null;
        };

        $once_fn = once($fn);

        self::assertNull($once_fn());
        self::assertNull($once_fn());
        self::assertSame(1, $call_count);
    }

    #[Test]
    public function onceWorksWithFalseReturnValue(): void
    {
        $call_count = 0;
        $fn = static function () use (&$call_count): bool {
            ++$call_count;
            return false;
        };

        $once_fn = once($fn);

        self::assertFalse($once_fn());
        self::assertFalse($once_fn());
        self::assertSame(1, $call_count);
    }
}
