<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Iterator;

use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Iterator\Iter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IterClassTest extends TestCase
{
    #[Test]
    #[DataProvider('providesIterCastTestCases')]
    public function castConvertsIterableToIterator(mixed $input, array $expected_array): void
    {
        /** @phpstan-ignore argument.templateType, argument.templateType (cannot resolve TKey, TValue for test input) */
        $iterator = Iter::cast($input);
        self::assertInstanceOf(\Iterator::class, $iterator);
        self::assertSame($expected_array, \iterator_to_array($iterator));
    }

    /**
     * @return \Generator<array{mixed, array<mixed>}>
     */
    public static function providesIterCastTestCases(): \Generator
    {
        yield 'array' => [
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 2],
        ];

        yield 'empty_array' => [
            [],
            [],
        ];

        yield 'iterator' => [
            new \ArrayIterator(['x' => 10]),
            ['x' => 10],
        ];

        yield 'generator' => [
            (function () {
                yield 'gen' => 'value';
            })(),
            ['gen' => 'value'],
        ];

        yield 'iterator_aggregate' => [
            new class implements \IteratorAggregate {
                public function getIterator(): \Iterator
                {
                    return new \ArrayIterator(['agg' => 'test']);
                }
            },
            ['agg' => 'test'],
        ];

        yield 'arrayable' => [
            new class implements Arrayable {
                public function toArray(): array
                {
                    return ['arrayable' => 'data'];
                }
            },
            ['arrayable' => 'data'],
        ];
    }

    #[Test]
    public function castWithArrayableAndTraversableFavorsTraversable(): void
    {
        $combined = new class implements Arrayable, \IteratorAggregate {
            public function toArray(): array
            {
                return ['from_array' => 1];
            }

            public function getIterator(): \Iterator
            {
                return new \ArrayIterator(['from_iterator' => 2]);
            }
        };

        $iterator = Iter::cast($combined);
        self::assertInstanceOf(\IteratorIterator::class, $iterator);
        self::assertSame(['from_iterator' => 2], \iterator_to_array($iterator));
    }

    #[Test]
    #[DataProvider('providesFirstLastTestCases')]
    public function firstAndLastReturnCorrectValues(iterable $input, mixed $expected_first, mixed $expected_last): void
    {
        self::assertSame($expected_first, Iter::first($input));
        self::assertSame($expected_last, Iter::last($input));
    }

    /**
     * @return \Generator<array{iterable<mixed>, mixed, mixed}>
     */
    public static function providesFirstLastTestCases(): \Generator
    {
        yield 'empty' => [[], null, null];
        yield 'single' => [['only'], 'only', 'only'];
        yield 'multiple' => [['first', 'middle', 'last'], 'first', 'last'];
        yield 'associative' => [['a' => 1, 'b' => 2, 'c' => 3], 1, 3];
        yield 'generator' => [
            (function () {
                yield 'start';
                yield 'end';
            })(),
            'start',
            'end',
        ];
    }

    #[Test]
    public function mapTransformsElementsWithKeyAndValue(): void
    {
        $input = ['name' => 'John', 'age' => 30];
        $callback = static fn(string|int $value, string $key): string => $key . '=' . $value;

        $generator = Iter::map($callback, $input);
        self::assertInstanceOf(\Generator::class, $generator);
        self::assertSame(['name' => 'name=John', 'age' => 'age=30'], \iterator_to_array($generator));
    }

    #[Test]
    public function mapHandlesEmptyInput(): void
    {
        $generator = Iter::map(fn($v, $k): string => (string)$v, []);
        self::assertSame([], \iterator_to_array($generator));
    }

    #[Test]
    public function amapConvertsToArrayDirectly(): void
    {
        $input = [1, 2, 3];
        $callback = static fn(int $value): int => $value * 2;

        $result = Iter::amap($callback, $input);
        self::assertSame([0 => 2, 1 => 4, 2 => 6], $result);
    }

    #[Test]
    public function amapHandlesEmptyInput(): void
    {
        $result = Iter::amap(fn($v): string => (string)$v, []);
        self::assertSame([], $result);
    }

    #[Test]
    public function chainCombinesMultipleIterables(): void
    {
        $first = ['a', 'b'];
        $second = new \ArrayIterator(['c', 'd']);
        $third = (function () {
            yield 'e';
            yield 'f';
        })();

        $chained = Iter::chain($first, $second, $third);
        self::assertInstanceOf(\AppendIterator::class, $chained);

        $result = [];
        foreach ($chained as $value) {
            $result[] = $value;
        }

        self::assertSame(['a', 'b', 'c', 'd', 'e', 'f'], $result);
    }

    #[Test]
    public function chainWithNoArguments(): void
    {
        /** @phpstan-ignore argument.templateType, argument.templateType (cannot resolve TKey, TValue if not passed) */
        $chained = Iter::chain();
        self::assertSame([], \iterator_to_array($chained));
    }

    #[Test]
    public function generateYieldsFromIterable(): void
    {
        $input = ['x' => 1, 'y' => 2];
        $generator = Iter::generate($input);

        self::assertInstanceOf(\Generator::class, $generator);
        self::assertSame($input, \iterator_to_array($generator));
    }

    #[Test]
    public function generateHandlesEmptyIterable(): void
    {
        $generator = Iter::generate([]);
        self::assertSame([], \iterator_to_array($generator));
    }

    #[Test]
    #[DataProvider('providesAnyValueTestCases')]
    public function anyValueReturnsTrueWhenCallbackMatches(iterable $input, callable $callback, bool $expected): void
    {
        self::assertSame($expected, Iter::anyValue($callback, $input));
    }

    /**
     * @return \Generator<array{iterable<mixed>, callable, bool}>
     */
    public static function providesAnyValueTestCases(): \Generator
    {
        yield 'found' => [
            [1, 2, 3, 4],
            static fn(int $value): bool => $value > 3,
            true,
        ];

        yield 'not_found' => [
            [1, 2, 3],
            static fn(int $value): bool => $value > 10,
            false,
        ];

        yield 'empty' => [
            [],
            static fn(mixed $value): bool => true,
            false,
        ];

        yield 'first_match' => [
            ['match', 'no', 'no'],
            static fn(string $value): bool => $value === 'match',
            true,
        ];
    }

    #[Test]
    #[DataProvider('providesAnyKeyTestCases')]
    public function anyKeyReturnsTrueWhenCallbackMatches(iterable $input, callable $callback, bool $expected): void
    {
        self::assertSame($expected, Iter::anyKey($callback, $input));
    }

    /**
     * @return \Generator<array{iterable<mixed>, callable, bool}>
     */
    public static function providesAnyKeyTestCases(): \Generator
    {
        yield 'found' => [
            ['a' => 1, 'b' => 2, 'target' => 3],
            static fn(string $key): bool => $key === 'target',
            true,
        ];

        yield 'not_found' => [
            ['a' => 1, 'b' => 2],
            static fn(string $key): bool => $key === 'missing',
            false,
        ];

        yield 'empty' => [
            [],
            static fn(mixed $key): bool => true,
            false,
        ];

        yield 'numeric_key' => [
            [0 => 'a', 5 => 'b', 10 => 'c'],
            static fn(int $key): bool => $key === 5,
            true,
        ];
    }

    #[Test]
    public function anyValueStopsOnFirstMatch(): void
    {
        $called = [];
        $iterable = [1, 2, 3, 4, 5];

        $result = Iter::anyValue(function (int $value) use (&$called): bool {
            $called[] = $value;
            return $value === 3;
        }, $iterable);

        self::assertTrue($result);
        self::assertSame([1, 2, 3], $called); // Should stop at 3
    }

    #[Test]
    public function anyKeyStopsOnFirstMatch(): void
    {
        $called = [];
        $iterable = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

        $result = Iter::anyKey(function (string $key) use (&$called): bool {
            $called[] = $key;
            return $key === 'b';
        }, $iterable);

        self::assertTrue($result);
        self::assertSame(['a', 'b'], $called); // Should stop at 'b'
    }
}
