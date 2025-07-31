<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Array;

use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Array\NullableArrayAccess;
use PhoneBurner\Pinch\Iterator\Sort\Order;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Array\array_any_key;
use function PhoneBurner\Pinch\Array\array_any_value;
use function PhoneBurner\Pinch\Array\array_cast;
use function PhoneBurner\Pinch\Array\array_convert_nested_objects;
use function PhoneBurner\Pinch\Array\array_dot;
use function PhoneBurner\Pinch\Array\array_dot_flatten;
use function PhoneBurner\Pinch\Array\array_first;
use function PhoneBurner\Pinch\Array\array_get;
use function PhoneBurner\Pinch\Array\array_has;
use function PhoneBurner\Pinch\Array\array_has_all;
use function PhoneBurner\Pinch\Array\array_has_any;
use function PhoneBurner\Pinch\Array\array_is_sorted;
use function PhoneBurner\Pinch\Array\array_last;
use function PhoneBurner\Pinch\Array\array_map_with_key;
use function PhoneBurner\Pinch\Array\array_value;
use function PhoneBurner\Pinch\Array\array_wrap;

#[CoversFunction('PhoneBurner\Pinch\Array\array_cast')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_convert_nested_objects')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_dot')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_first')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_get')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_has')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_has_all')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_has_any')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_last')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_map_with_key')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_value')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_wrap')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_any_key')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_any_value')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_dot_flatten')]
#[CoversFunction('PhoneBurner\Pinch\Array\array_is_sorted')]
final class ArrayFunctionsTest extends TestCase
{
    #[Test]
    public function arrCastReturnsArrayForArray(): void
    {
        $array = ['foo' => 'bar'];
        self::assertSame($array, array_cast($array));
    }

    #[Test]
    public function arrCastConvertsArrayableToArray(): void
    {
        $arrayable = new class implements Arrayable {
            public function toArray(): array
            {
                return ['test' => 'value'];
            }
        };

        self::assertSame(['test' => 'value'], array_cast($arrayable));
    }

    #[Test]
    public function arrCastConvertsTraversableToArray(): void
    {
        $iterator = new \ArrayIterator(['a' => 1, 'b' => 2]);
        self::assertSame(['a' => 1, 'b' => 2], array_cast($iterator));
    }

    #[Test]
    public function arrFirstReturnsFirstValue(): void
    {
        self::assertSame('first', array_first(['first', 'second', 'third']));
        self::assertSame('value', array_first(['key' => 'value']));
    }

    #[Test]
    public function arrFirstReturnsNullForEmptyArray(): void
    {
        self::assertNull(array_first([]));
    }

    #[Test]
    public function arrLastReturnsLastValue(): void
    {
        self::assertSame('third', array_last(['first', 'second', 'third']));
        self::assertSame('value', array_last(['key' => 'value']));
    }

    #[Test]
    public function arrLastReturnsNullForEmptyArray(): void
    {
        self::assertNull(array_last([]));
    }

    #[Test]
    public function arrHasReturnsTrueWhenKeyExists(): void
    {
        $array = ['foo' => 'bar', 'nested' => ['key' => 'value']];

        self::assertTrue(array_has('foo', $array));
        self::assertTrue(array_has('nested.key', $array));
    }

    #[Test]
    public function arrHasReturnsFalseWhenKeyDoesNotExist(): void
    {
        $array = ['foo' => 'bar'];

        self::assertFalse(array_has('missing', $array));
        self::assertFalse(array_has('foo.missing', $array));
    }

    #[Test]
    public function arrGetReturnsValueWhenKeyExists(): void
    {
        $array = ['foo' => 'bar', 'nested' => ['key' => 'value']];

        self::assertSame('bar', array_get('foo', $array));
        self::assertSame('value', array_get('nested.key', $array));
    }

    #[Test]
    public function arrGetReturnsNullWhenKeyDoesNotExist(): void
    {
        $array = ['foo' => 'bar'];

        self::assertNull(array_get('missing', $array));
        self::assertNull(array_get('foo.missing', $array));
    }

    #[Test]
    public function arrWrapWrapsNonArrayableValues(): void
    {
        self::assertSame(['string'], array_wrap('string'));
        self::assertSame([42], array_wrap(42));
    }

    #[Test]
    public function arrWrapReturnsArrayableAsArray(): void
    {
        $array = ['foo' => 'bar'];
        self::assertSame($array, array_wrap($array));
    }

    #[Test]
    public function arrMapAppliesCallbackToElements(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = array_map_with_key(fn($value, $key): int => $value * 2, $array);

        self::assertSame(['a' => 2, 'b' => 4, 'c' => 6], $result);
    }

    #[Test]
    public function arrDotFlattensNestedArray(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'city' => 'NYC',
                ],
            ],
        ];

        $expected = [
            'user.name' => 'John',
            'user.address.city' => 'NYC',
        ];

        self::assertSame($expected, array_dot($array));
    }

    #[Test]
    public function arrDotHandlesEmptyArray(): void
    {
        self::assertSame([], array_dot([]));
    }

    #[Test]
    public function arrDotHandlesArrayWithEmptyNestedArrays(): void
    {
        $array = [
            'user' => [],
            'settings' => [
                'theme' => 'dark',
                'notifications' => [],
            ],
        ];

        $expected = [
            'user' => [],
            'settings.theme' => 'dark',
            'settings.notifications' => [],
        ];

        self::assertSame($expected, array_dot($array));
    }

    #[Test]
    public function arrDotFlattenHandlesDeepNesting(): void
    {
        $results = [];
        $array = [
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ];

        array_dot_flatten($results, $array, 'prefix.');

        $expected = [
            'prefix.level1.level2.level3' => 'value',
        ];

        self::assertSame($expected, $results);
    }

    #[Test]
    public function arrHasAnyReturnsTrueWhenAnyPathExists(): void
    {
        $array = ['foo' => 'bar', 'nested' => ['key' => 'value']];

        self::assertTrue(array_has_any(['foo', 'missing'], $array));
        self::assertTrue(array_has_any(['nested.key', 'missing'], $array));
        self::assertTrue(array_has_any(['foo'], $array));
    }

    #[Test]
    public function arrHasAnyReturnsFalseWhenNoPathsExist(): void
    {
        $array = ['foo' => 'bar'];

        self::assertFalse(array_has_any(['missing', 'also_missing'], $array));
        self::assertFalse(array_has_any(['foo.missing'], $array));
        self::assertFalse(array_has_any([], $array));
    }

    #[Test]
    public function arrHasAnyWorksWithArrayAccess(): void
    {
        $array_access = new NullableArrayAccess(['foo' => 'bar', 'nested' => ['key' => 'value']]);

        self::assertTrue(array_has_any(['foo'], $array_access));
        self::assertFalse(array_has_any(['missing'], $array_access));
    }

    #[Test]
    public function arrHasAllReturnsTrueWhenAllPathsExist(): void
    {
        $array = ['foo' => 'bar', 'nested' => ['key' => 'value']];

        self::assertTrue(array_has_all(['foo', 'nested.key'], $array));
        self::assertTrue(array_has_all(['foo'], $array));
        self::assertTrue(array_has_all([], $array)); // Empty array should return true
    }

    #[Test]
    public function arrHasAllReturnsFalseWhenAnyPathMissing(): void
    {
        $array = ['foo' => 'bar'];

        self::assertFalse(array_has_all(['foo', 'missing'], $array));
        self::assertFalse(array_has_all(['missing'], $array));
    }

    #[Test]
    public function arrHasAllWorksWithArrayAccess(): void
    {
        $array_access = new NullableArrayAccess(['foo' => 'bar', 'nested' => ['key' => 'value']]);

        self::assertTrue(array_has_all(['foo'], $array_access));
        self::assertFalse(array_has_all(['foo', 'missing'], $array_access));
    }

    #[Test]
    public function arrGetWorksWithArrayAccess(): void
    {
        $array_access = new NullableArrayAccess(['foo' => 'bar', 'nested' => ['key' => 'value']]);

        self::assertSame('bar', array_get('foo', $array_access));
        self::assertSame('value', array_get('nested.key', $array_access));
        self::assertNull(array_get('missing', $array_access));
    }

    #[Test]
    public function arrGetHandlesNullValues(): void
    {
        $array = ['null_value' => null, 'nested' => ['null_key' => null]];

        self::assertNull(array_get('null_value', $array));
        self::assertNull(array_get('nested.null_key', $array));
        self::assertNull(array_get('missing', $array));
    }

    #[Test]
    public function arrGetHandlesKeyWithDotsDirectly(): void
    {
        $array = ['key.with.dots' => 'direct_value', 'key' => ['with' => ['dots' => 'nested_value']]];

        // Should return direct key first
        self::assertSame('direct_value', array_get('key.with.dots', $array));
    }

    #[Test]
    public function arrHasHandlesNullValues(): void
    {
        $array = ['null_value' => null, 'nested' => ['null_key' => null]];

        // array_has returns false for null values since array_get returns null
        self::assertFalse(array_has('null_value', $array));
        self::assertFalse(array_has('nested.null_key', $array));
    }

    #[Test]
    public function arrHasWorksWithArrayAccess(): void
    {
        $array_access = new NullableArrayAccess(['foo' => 'bar', 'nested' => ['key' => 'value']]);

        self::assertTrue(array_has('foo', $array_access));
        self::assertTrue(array_has('nested.key', $array_access));
        self::assertFalse(array_has('missing', $array_access));
    }

    #[Test]
    public function arrCastHandlesGeneratorFunction(): void
    {
        $generator_fn = function (): \Generator {
            yield 'key1' => 'value1';
            yield 'key2' => 'value2';
        };

        $result = array_cast($generator_fn());
        self::assertSame(['key1' => 'value1', 'key2' => 'value2'], $result);
    }

    #[Test]
    public function arrValueRecursivelyConvertsArrayable(): void
    {
        $inner_arrayable = new class implements Arrayable {
            public function toArray(): array
            {
                return ['inner' => 'value'];
            }
        };

        $outer_arrayable = new readonly class ($inner_arrayable) implements Arrayable {
            /**
             * @param Arrayable<array-key, mixed> $inner
             */
            public function __construct(private Arrayable $inner)
            {
            }

            public function toArray(): array
            {
                return ['outer' => $this->inner];
            }
        };

        $result = array_value($outer_arrayable);
        $expected = ['outer' => ['inner' => 'value']];

        self::assertSame($expected, $result);
    }

    #[Test]
    public function arrValueHandlesScalarValues(): void
    {
        self::assertSame('string', array_value('string'));
        self::assertSame(42, array_value(42));
        self::assertSame(3.14, array_value(3.14));
        self::assertTrue(array_value(true));
        self::assertNull(array_value(null));
    }

    #[Test]
    public function arrValueHandlesTraversable(): void
    {
        $iterator = new \ArrayIterator(['key' => 'value']);
        $result = array_value($iterator);

        self::assertSame(['key' => 'value'], $result);
    }

    #[Test]
    public function arrConvertNestedObjectsConvertsSuccessfully(): void
    {
        $object = new \stdClass();
        $object->name = 'John';
        $object->nested = new \stdClass();
        $object->nested->city = 'NYC';

        $result = array_convert_nested_objects($object);
        $expected = [
            'name' => 'John',
            'nested' => [
                'city' => 'NYC',
            ],
        ];

        self::assertSame($expected, $result);
    }

    #[Test]
    public function arrConvertNestedObjectsHandlesArrayInput(): void
    {
        $input = ['key' => 'value'];
        $result = array_convert_nested_objects($input);

        self::assertSame(['key' => 'value'], $result);
    }

    #[Test]
    public function arrConvertNestedObjectsHandlesScalarInput(): void
    {
        $result = array_convert_nested_objects('string');
        self::assertSame(['string'], $result);

        $result = array_convert_nested_objects(42);
        self::assertSame([42], $result);
    }

    #[Test]
    public function arrConvertNestedObjectsHandlesJsonException(): void
    {
        // Create a resource that cannot be JSON encoded
        $resource = \fopen('php://memory', 'r');
        if ($resource !== false) {
            $result = array_convert_nested_objects($resource);
            \fclose($resource);
        } else {
            $result = [];
        }

        self::assertSame([], $result);
    }

    #[Test]
    #[DataProvider('sortedArrayProvider')]
    public function arrIsSortedDetectsCorrectly(array $input, Order $order, bool $expected): void
    {
        self::assertSame($expected, array_is_sorted($input, $order));
    }

    public static function sortedArrayProvider(): \Iterator
    {
        yield 'empty array ascending' => [[], Order::Ascending, true];
        yield 'single element ascending' => [[1], Order::Ascending, true];
        yield 'ascending order' => [[1, 2, 3, 4], Order::Ascending, true];
        yield 'ascending with duplicates' => [[1, 2, 2, 3], Order::Ascending, true];
        yield 'not ascending' => [[1, 3, 2, 4], Order::Ascending, false];
        yield 'descending order' => [[4, 3, 2, 1], Order::Descending, true];
        yield 'descending with duplicates' => [[3, 2, 2, 1], Order::Descending, true];
        yield 'not descending' => [[4, 2, 3, 1], Order::Descending, false];
        yield 'strings ascending' => [['a', 'b', 'c'], Order::Ascending, true];
        yield 'strings descending' => [['c', 'b', 'a'], Order::Descending, true];
    }

    #[Test]
    public function arrIsSortedDefaultsToAscending(): void
    {
        self::assertTrue(array_is_sorted([1, 2, 3]));
        self::assertFalse(array_is_sorted([3, 2, 1]));
    }

    #[Test]
    public function arrAnyValueReturnsTrueWhenCallbackMatches(): void
    {
        $iterable = [1, 2, 3, 4, 5];
        $callback = fn(int $value): bool => $value > 3;

        self::assertTrue(array_any_value($iterable, $callback));
    }

    #[Test]
    public function arrAnyValueReturnsFalseWhenNoCallbackMatches(): void
    {
        $iterable = [1, 2, 3];
        $callback = fn(int $value): bool => $value > 5;

        self::assertFalse(array_any_value($iterable, $callback));
    }

    #[Test]
    public function arrAnyValueWorksWithEmptyIterable(): void
    {
        $callback = fn(mixed $value): bool => true;

        self::assertFalse(array_any_value([], $callback));
    }

    #[Test]
    public function arrAnyValueWorksWithGenerator(): void
    {
        $generator = function (): \Generator {
            yield 1;
            yield 2;
            yield 3;
        };

        $callback = fn(int $value): bool => $value === 2;

        self::assertTrue(array_any_value($generator(), $callback));
    }

    #[Test]
    public function arrAnyKeyReturnsTrueWhenCallbackMatches(): void
    {
        $iterable = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $callback = fn(string $key): bool => $key === 'bar';

        self::assertTrue(array_any_key($iterable, $callback));
    }

    #[Test]
    public function arrAnyKeyReturnsFalseWhenNoCallbackMatches(): void
    {
        $iterable = ['foo' => 1, 'bar' => 2];
        $callback = fn(string $key): bool => $key === 'missing';

        self::assertFalse(array_any_key($iterable, $callback));
    }

    #[Test]
    public function arrAnyKeyWorksWithEmptyIterable(): void
    {
        $callback = fn(mixed $key): bool => true;

        self::assertFalse(array_any_key([], $callback));
    }

    #[Test]
    public function arrAnyKeyWorksWithNumericKeys(): void
    {
        $iterable = ['a', 'b', 'c'];
        $callback = fn(int $key): bool => $key === 1;

        self::assertTrue(array_any_key($iterable, $callback));
    }

    #[Test]
    public function arrAnyKeyWorksWithGenerator(): void
    {
        $generator = function (): \Generator {
            yield 'key1' => 'value1';
            yield 'key2' => 'value2';
        };

        $callback = fn(string $key): bool => $key === 'key2';

        self::assertTrue(array_any_key($generator(), $callback));
    }
}
