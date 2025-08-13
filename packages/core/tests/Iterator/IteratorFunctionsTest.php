<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Iterator;

use PhoneBurner\Pinch\Array\Arrayable;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Iterator\iter_amap;
use function PhoneBurner\Pinch\Iterator\iter_any_key;
use function PhoneBurner\Pinch\Iterator\iter_any_value;
use function PhoneBurner\Pinch\Iterator\iter_cast;
use function PhoneBurner\Pinch\Iterator\iter_chain;
use function PhoneBurner\Pinch\Iterator\iter_first;
use function PhoneBurner\Pinch\Iterator\iter_generate;
use function PhoneBurner\Pinch\Iterator\iter_last;
use function PhoneBurner\Pinch\Iterator\iter_map;

#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_amap')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_any_key')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_any_value')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_cast')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_chain')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_first')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_generate')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_last')]
#[CoversFunction('PhoneBurner\Pinch\Iterator\iter_map')]
final class IteratorFunctionsTest extends TestCase
{
    #[Test]
    public function iterCastConvertsArrayToIterator(): void
    {
        $array = ['a' => 1, 'b' => 2];
        $iterator = iter_cast($array);

        self::assertInstanceOf(\Iterator::class, $iterator);
        self::assertSame($array, \iterator_to_array($iterator));
    }

    #[Test]
    public function iterCastReturnsIteratorAsIs(): void
    {
        $iterator = new \ArrayIterator(['a' => 1, 'b' => 2]);
        $result = iter_cast($iterator);

        self::assertSame($iterator, $result);
    }

    #[Test]
    public function iterCastReturnsGeneratorAsIs(): void
    {
        $generator = (function () {
            yield 'a' => 1;
            yield 'b' => 2;
        })();
        $iterator = iter_cast($generator);

        // Generator implements Iterator, so it should be returned as-is
        self::assertSame($generator, $iterator);
        self::assertSame(['a' => 1, 'b' => 2], \iterator_to_array($iterator));
    }

    #[Test]
    public function iterCastConvertsArrayableToIterator(): void
    {
        $arrayable = new class implements Arrayable {
            public function toArray(): array
            {
                return ['test' => 'value'];
            }
        };

        $iterator = iter_cast($arrayable);
        self::assertInstanceOf(\Iterator::class, $iterator);
        self::assertSame(['test' => 'value'], \iterator_to_array($iterator));
    }

    #[Test]
    public function iterFirstReturnsFirstValue(): void
    {
        self::assertSame(1, iter_first([1, 2, 3]));
        self::assertSame('a', iter_first(['a', 'b', 'c']));
    }

    #[Test]
    public function iterFirstReturnsNullForEmptyIterable(): void
    {
        self::assertNull(iter_first([]));
        self::assertNull(iter_first(new \EmptyIterator()));
    }

    #[Test]
    public function iterLastReturnsLastValue(): void
    {
        self::assertSame(3, iter_last([1, 2, 3]));
        self::assertSame('c', iter_last(['a', 'b', 'c']));
    }

    #[Test]
    public function iterLastReturnsNullForEmptyIterable(): void
    {
        self::assertNull(iter_last([]));
        self::assertNull(iter_last(new \EmptyIterator()));
    }

    #[Test]
    public function iterMapAppliesCallbackToElements(): void
    {
        $iterable = [1, 2, 3];
        $result = iter_map(fn($value, $key): int => $value * 2, $iterable);

        self::assertInstanceOf(\Generator::class, $result);
        self::assertSame([0 => 2, 1 => 4, 2 => 6], \iterator_to_array($result));
    }

    #[Test]
    public function iterAmapReturnsArray(): void
    {
        $iterable = [1, 2, 3];
        $result = iter_amap(fn($value): int => $value * 2, $iterable);

        self::assertSame([0 => 2, 1 => 4, 2 => 6], $result);
    }

    #[Test]
    public function iterChainCombinesIterables(): void
    {
        $first = [1, 2];
        $second = [3, 4];
        $chained = iter_chain($first, $second);

        self::assertInstanceOf(\AppendIterator::class, $chained);

        // Collect all values by iterating once
        $result = [];
        foreach ($chained as $value) {
            $result[] = $value;
        }

        self::assertSame([1, 2, 3, 4], $result);
    }

    #[Test]
    public function iterGenerateYieldsFromIterable(): void
    {
        $array = [1, 2, 3];
        $generator = iter_generate($array);

        self::assertInstanceOf(\Generator::class, $generator);
        self::assertSame($array, \iterator_to_array($generator));
    }

    #[Test]
    public function iterAnyValueReturnsTrueWhenCallbackMatchesValue(): void
    {
        $iterable = [1, 2, 3, 4];
        $result = iter_any_value(fn($value): bool => $value > 3, $iterable);

        self::assertTrue($result);
    }

    #[Test]
    public function iterAnyValueReturnsFalseWhenNoValueMatches(): void
    {
        $iterable = [1, 2, 3, 4, 5];
        /** @phpstan-ignore greater.alwaysFalse (intentional defect for testing) */
        $result = iter_any_value(fn($value): false => $value > 10, $iterable);

        self::assertFalse($result);
    }

    #[Test]
    public function iterAnyKeyReturnsTrueWhenCallbackMatchesKey(): void
    {
        $iterable = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = iter_any_key(fn($key): bool => $key === 'b', $iterable);

        self::assertTrue($result);
    }

    #[Test]
    public function iterAnyKeyReturnsFalseWhenNoKeyMatches(): void
    {
        $iterable = ['a' => 1, 'b' => 2, 'c' => 3];
        /** @phpstan-ignore identical.alwaysFalse (intentional defect for testing) */
        $result = iter_any_key(fn($key): false => $key === 'missing', $iterable);

        self::assertFalse($result);
    }

    #[Test]
    public function iterAnyValueReturnsFalseWithEmptyIterable(): void
    {
        $result = iter_any_value(fn($value): bool => $value > 0, []);
        self::assertFalse($result);
    }

    #[Test]
    public function iterAnyKeyReturnsFalseWithEmptyIterable(): void
    {
        /** @phpstan-ignore identical.alwaysFalse (testing edge case with empty iterable) */
        $result = iter_any_key(fn($key): bool => $key === 'any', []);
        self::assertFalse($result);
    }

    #[Test]
    public function iterCastHandlesTraversableThatIsNotIterator(): void
    {
        $traversable = new class implements \Traversable, \IteratorAggregate {
            public function getIterator(): \Iterator
            {
                return new \ArrayIterator(['x' => 10, 'y' => 20]);
            }
        };

        /** @phpstan-ignore argument.templateType (cannot resolve TKey for mixed Traversable test case) */
        $iterator = iter_cast($traversable);
        self::assertInstanceOf(\IteratorIterator::class, $iterator);
        self::assertSame(['x' => 10, 'y' => 20], \iterator_to_array($iterator));
    }

    #[Test]
    public function iterCastHandlesArrayableWithTraversable(): void
    {
        $arrayable_traversable = new class implements Arrayable, \Traversable, \IteratorAggregate {
            public function toArray(): array
            {
                return ['from_array' => 1];
            }

            public function getIterator(): \Iterator
            {
                return new \ArrayIterator(['from_iterator' => 2]);
            }
        };

        // When an object is both Traversable and Arrayable, iter_cast should treat it as Traversable
        /** @phpstan-ignore argument.templateType (cannot resolve template for mixed Arrayable/Traversable) */
        $iterator = iter_cast($arrayable_traversable);
        self::assertInstanceOf(\IteratorIterator::class, $iterator);
        self::assertSame(['from_iterator' => 2], \iterator_to_array($iterator));
    }

    #[Test]
    public function iterMapHandlesEmptyIterable(): void
    {
        $result = iter_map(fn($value, $key): string => $key . '=' . $value, []);
        self::assertInstanceOf(\Generator::class, $result);
        self::assertSame([], \iterator_to_array($result));
    }

    #[Test]
    public function iterAmapHandlesEmptyIterable(): void
    {
        $result = iter_amap(fn($value): string => (string)$value, []);
        self::assertSame([], $result);
    }

    #[Test]
    public function iterChainWithArrayableObjects(): void
    {
        $arrayable1 = new class implements Arrayable {
            public function toArray(): array
            {
                return ['a' => 1];
            }
        };

        $arrayable2 = new class implements Arrayable {
            public function toArray(): array
            {
                return ['b' => 2];
            }
        };

        $chained = iter_chain($arrayable1, $arrayable2);
        self::assertInstanceOf(\AppendIterator::class, $chained);

        $result = [];
        foreach ($chained as $key => $value) {
            $result[$key] = $value;
        }

        // AppendIterator preserves keys from each source
        self::assertSame(['a' => 1, 'b' => 2], $result);
    }

    #[Test]
    public function iterChainWithMixedIterableTypes(): void
    {
        $array = [1, 2];
        $generator = (function () {
            yield 3;
            yield 4;
        })();
        $iterator = new \ArrayIterator([5, 6]);

        $chained = iter_chain($array, $generator, $iterator);
        $result = [];
        foreach ($chained as $value) {
            $result[] = $value;
        }

        self::assertSame([1, 2, 3, 4, 5, 6], $result);
    }

    #[Test]
    public function iterGenerateHandlesGenerator(): void
    {
        $source = (function () {
            yield 'x' => 100;
            yield 'y' => 200;
        })();

        $generator = iter_generate($source);
        self::assertInstanceOf(\Generator::class, $generator);
        self::assertSame(['x' => 100, 'y' => 200], \iterator_to_array($generator));
    }

    #[Test]
    public function iterGenerateHandlesEmptyIterable(): void
    {
        $generator = iter_generate([]);
        self::assertInstanceOf(\Generator::class, $generator);
        self::assertSame([], \iterator_to_array($generator));
    }

    #[Test]
    public function iterAnyValueWithGenerator(): void
    {
        $generator = (function () {
            yield 1;
            yield 2;
            yield 3;
        })();

        $result = iter_any_value(fn($value): bool => $value === 2, $generator);
        self::assertTrue($result);
    }

    #[Test]
    public function iterAnyKeyWithGenerator(): void
    {
        $generator = (function () {
            yield 'first' => 1;
            yield 'second' => 2;
            yield 'third' => 3;
        })();

        $result = iter_any_key(fn($key): bool => $key === 'second', $generator);
        self::assertTrue($result);
    }

    #[Test]
    public function iterAnyValueEarlyReturn(): void
    {
        // Test that iter_any_value returns as soon as a matching value is found
        $called = [];
        $iterable = [1, 2, 3, 4, 5];
        $result = iter_any_value(function ($value) use (&$called): bool {
            $called[] = $value;
            return $value === 3;
        }, $iterable);

        self::assertTrue($result);
        self::assertSame([1, 2, 3], $called); // Should stop at 3
    }

    #[Test]
    public function iterAnyKeyEarlyReturn(): void
    {
        // Test that iter_any_key returns as soon as a matching key is found
        $called = [];
        $iterable = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $result = iter_any_key(function ($key) use (&$called): bool {
            $called[] = $key;
            return $key === 'b';
        }, $iterable);

        self::assertTrue($result);
        self::assertSame(['a', 'b'], $called); // Should stop at 'b'
    }
}
