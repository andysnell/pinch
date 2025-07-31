<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Iterator\Sort;

use ArrayIterator;
use Generator;
use PhoneBurner\Pinch\Iterator\Sort\Comparison;
use PhoneBurner\Pinch\Iterator\Sort\Order;
use PhoneBurner\Pinch\Iterator\Sort\Sort;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class SortTest extends TestCase
{
    #[Test]
    #[DataProvider('providesListSortTestCases')]
    public function listSortsArraysCorrectly(
        iterable $input,
        array $expected,
        Order|callable $order = Order::Ascending,
        Comparison $type = Comparison::Regular,
    ): void {
        $sorted = Sort::list($input, $order, $type);
        self::assertSame($expected, $sorted);
        if ($input !== $expected && \is_array($input)) {
            self::assertNotSame($input, $sorted);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function providesListSortTestCases(): Generator
    {
        yield 'empty_array' => [
            [],
            [],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'list_ascending_regular' => [
            [3, 1, 4, 2],
            [1, 2, 3, 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'list_ascending_string' => [
            ['10', '1', '2'],
            ['1', '10', '2'],
            Order::Ascending,
            Comparison::String,
        ];

        yield 'list_ascending_natural' => [
            ['10', '1', '2'],
            ['1', '2', '10'],
            Order::Ascending,
            Comparison::Natural,
        ];

        yield 'list_ascending_numeric' => [
            ['10', '1', '2'],
            ['1', '2', '10'],
            Order::Ascending,
            Comparison::Numeric,
        ];

        yield 'list_ascending_case_insensitive' => [
            ['B', 'a', 'C', 'b'],
            ['a', 'B', 'b', 'C'],
            Order::Ascending,
            Comparison::StringCaseInsensitive,
        ];

        yield 'list_descending_regular' => [
            [3, 1, 4, 2],
            [4, 3, 2, 1],
            Order::Descending,
            Comparison::Regular,
        ];

        yield 'list_descending_string' => [
            ['10', '1', '2'],
            ['2', '10', '1'],
            Order::Descending,
            Comparison::String,
        ];

        yield 'list_descending_natural' => [
            ['10', '1', '2'],
            ['10', '2', '1'],
            Order::Descending,
            Comparison::Natural,
        ];

        yield 'list_mixed_types_ascending' => [
            [3, '1', 4.5, '2'],
            ['1', '2', 3, 4.5],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'list_special_chars_natural' => [
            ['file10.txt', 'file1.txt', 'file2.txt'],
            ['file1.txt', 'file2.txt', 'file10.txt'],
            Order::Ascending,
            Comparison::Natural,
        ];

        yield 'list_with_custom_callback' => [
            [3, 1, 4, 2],
            [1, 2, 3, 4],
            static fn($a, $b): int => $a <=> $b,
            Comparison::Regular,
        ];

        yield 'list_with_custom_callback_descending' => [
            [3, 1, 4, 2],
            [4, 3, 2, 1],
            static fn($a, $b): int => $b <=> $a,
            Comparison::Regular,
        ];

        yield 'list_with_string_length_comparison' => [
            ['apple', 'banana', 'cherry', 'date'],
            ['date', 'apple', 'banana', 'cherry'],
            static fn($a, $b): int => \strlen((string)$a) <=> \strlen((string)$b) ?: $a <=> $b,
            Comparison::Regular,
        ];

        // Test with iterators
        yield 'list_with_iterator' => [
            new ArrayIterator([3, 1, 4, 2]),
            [1, 2, 3, 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'list_with_generator' => [
            (static function () {
                yield 3;
                yield 1;
                yield 4;
                yield 2;
            })(),
            [1, 2, 3, 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        // Test with objects
        $obj1 = new stdClass();
        $obj1->value = 3;
        $obj2 = new stdClass();
        $obj2->value = 1;
        $obj3 = new stdClass();
        $obj3->value = 4;

        yield 'list_with_objects' => [
            [$obj1, $obj2, $obj3],
            [$obj2, $obj1, $obj3],
            static fn($a, $b): int => $a->value <=> $b->value,
            Comparison::Regular,
        ];
    }

    #[Test]
    #[DataProvider('providesAssociativeSortTestCases')]
    public function associativeSortsArraysCorrectly(
        iterable $input,
        array $expected,
        Order|callable $order = Order::Ascending,
        Comparison $type = Comparison::Regular,
    ): void {
        $sorted = Sort::associative($input, $order, $type);
        self::assertSame($expected, $sorted);
        if ($input !== $expected && \is_array($input)) {
            self::assertNotSame($input, $sorted);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function providesAssociativeSortTestCases(): Generator
    {
        yield 'empty_array' => [
            [],
            [],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'associative_ascending_regular' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'associative_ascending_string' => [
            ['c' => '10', 'a' => '1', 'b' => '2'],
            ['a' => '1', 'c' => '10', 'b' => '2'],
            Order::Ascending,
            Comparison::String,
        ];

        yield 'associative_ascending_natural' => [
            ['c' => '10', 'a' => '1', 'b' => '2'],
            ['a' => '1', 'b' => '2', 'c' => '10'],
            Order::Ascending,
            Comparison::Natural,
        ];

        yield 'associative_descending_regular' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['d' => 4, 'c' => 3, 'b' => 2, 'a' => 1],
            Order::Descending,
            Comparison::Regular,
        ];

        yield 'associative_descending_string' => [
            ['c' => '10', 'a' => '1', 'b' => '2'],
            ['b' => '2', 'c' => '10', 'a' => '1'],
            Order::Descending,
            Comparison::String,
        ];

        yield 'associative_with_custom_callback' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            static fn($a, $b): int => $a <=> $b,
            Comparison::Regular,
        ];

        yield 'associative_with_custom_callback_descending' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['d' => 4, 'c' => 3, 'b' => 2, 'a' => 1],
            static fn($a, $b): int => $b <=> $a,
            Comparison::Regular,
        ];

        yield 'associative_with_string_length_comparison' => [
            ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry', 'd' => 'date'],
            ['d' => 'date', 'a' => 'apple', 'b' => 'banana', 'c' => 'cherry'],
            static fn($a, $b): int => \strlen((string)$a) <=> \strlen((string)$b) ?: $a <=> $b,
            Comparison::Regular,
        ];

        // Test with iterators
        yield 'associative_with_iterator' => [
            new ArrayIterator(['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2]),
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'associative_with_generator' => [
            (static function () {
                yield 'c' => 3;
                yield 'a' => 1;
                yield 'd' => 4;
                yield 'b' => 2;
            })(),
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        // Test with objects
        $obj1 = new stdClass();
        $obj1->value = 3;
        $obj2 = new stdClass();
        $obj2->value = 1;
        $obj3 = new stdClass();
        $obj3->value = 4;

        yield 'associative_with_objects' => [
            ['x' => $obj1, 'y' => $obj2, 'z' => $obj3],
            ['y' => $obj2, 'x' => $obj1, 'z' => $obj3],
            static fn($a, $b): int => $a->value <=> $b->value,
            Comparison::Regular,
        ];
    }

    #[Test]
    #[DataProvider('providesKeySortTestCases')]
    public function keySortsArraysByKeysCorrectly(
        iterable $input,
        array $expected,
        Order|callable $order = Order::Ascending,
        Comparison $type = Comparison::Regular,
    ): void {
        $sorted = Sort::key($input, $order, $type);
        self::assertSame($expected, $sorted);
        if ($input !== $expected && \is_array($input)) {
            self::assertNotSame($input, $sorted);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function providesKeySortTestCases(): Generator
    {
        yield 'empty_array' => [
            [],
            [],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'string_keys_ascending_regular' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'string_keys_descending_regular' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['d' => 4, 'c' => 3, 'b' => 2, 'a' => 1],
            Order::Descending,
            Comparison::Regular,
        ];

        yield 'numeric_keys_ascending_regular' => [
            [3 => 'c', 1 => 'a', 4 => 'd', 2 => 'b'],
            [1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd'],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'numeric_keys_descending_regular' => [
            [3 => 'c', 1 => 'a', 4 => 'd', 2 => 'b'],
            [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a'],
            Order::Descending,
            Comparison::Regular,
        ];

        yield 'mixed_keys_ascending_regular' => [
            [3 => 'c', 'a' => 1, '4' => 'd', 'b' => 2],
            [3 => 'c', 4 => 'd', 'a' => 1, 'b' => 2],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'mixed_keys_descending_regular' => [
            [3 => 'c', 'a' => 1, '4' => 'd', 'b' => 2],
            ['b' => 2, 'a' => 1, 4 => 'd', 3 => 'c'],
            Order::Descending,
            Comparison::Regular,
        ];

        yield 'string_keys_ascending_string' => [
            ['10' => 'j', '1' => 'a', '2' => 'b'],
            ['1' => 'a', '10' => 'j', '2' => 'b'],
            Order::Ascending,
            Comparison::String,
        ];

        yield 'string_keys_ascending_natural' => [
            ['10' => 'j', '1' => 'a', '2' => 'b'],
            ['1' => 'a', '2' => 'b', '10' => 'j'],
            Order::Ascending,
            Comparison::Natural,
        ];

        yield 'case_insensitive_keys_ascending' => [
            ['B' => 2, 'a' => 1, 'C' => 3, 'b' => 4],
            ['a' => 1, 'B' => 2, 'b' => 4, 'C' => 3],
            Order::Ascending,
            Comparison::StringCaseInsensitive,
        ];

        yield 'numeric_string_keys_ascending_numeric' => [
            ['10' => 'j', '1' => 'a', '2' => 'b'],
            ['1' => 'a', '2' => 'b', '10' => 'j'],
            Order::Ascending,
            Comparison::Numeric,
        ];

        yield 'key_with_custom_callback' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            static fn($a, $b): int => $a <=> $b,
            Comparison::Regular,
        ];

        yield 'key_with_custom_callback_descending' => [
            ['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2],
            ['d' => 4, 'c' => 3, 'b' => 2, 'a' => 1],
            static fn($a, $b): int => $b <=> $a,
            Comparison::Regular,
        ];

        yield 'key_with_string_length_comparison' => [
            ['short' => 1, 'very_long_key' => 2, 'medium_key' => 3],
            ['short' => 1, 'medium_key' => 3, 'very_long_key' => 2],
            static fn($a, $b): int => \strlen((string)$a) <=> \strlen((string)$b),
            Comparison::Regular,
        ];

        yield 'key_with_natural_order_comparison' => [
            ['file10.txt' => 10, 'file1.txt' => 1, 'file2.txt' => 2],
            ['file1.txt' => 1, 'file2.txt' => 2, 'file10.txt' => 10],
            static fn($a, $b): int => \strnatcmp((string)$a, (string)$b),
            Comparison::Regular,
        ];

        // Test with iterators
        yield 'key_with_iterator' => [
            new ArrayIterator(['c' => 3, 'a' => 1, 'd' => 4, 'b' => 2]),
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Order::Ascending,
            Comparison::Regular,
        ];

        yield 'key_with_generator' => [
            (static function () {
                yield 'c' => 3;
                yield 'a' => 1;
                yield 'd' => 4;
                yield 'b' => 2;
            })(),
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Order::Ascending,
            Comparison::Regular,
        ];
    }

    #[Test]
    public function listSortWithEmptyArrayDoesNotCallSortFunction(): void
    {
        // Test that empty arrays don't trigger sort operations
        $result = Sort::list([], Order::Ascending, Comparison::Regular);
        self::assertSame([], $result);
    }

    #[Test]
    public function associativeSortWithEmptyArrayDoesNotCallSortFunction(): void
    {
        // Test that empty arrays don't trigger sort operations
        $result = Sort::associative([], Order::Descending, Comparison::String);
        self::assertSame([], $result);
    }

    #[Test]
    public function keySortWithEmptyArrayDoesNotCallSortFunction(): void
    {
        // Test that empty arrays don't trigger sort operations
        $result = Sort::key([], Order::Ascending, Comparison::Natural);
        self::assertSame([], $result);
    }

    #[Test]
    public function listSortOriginalArrayUnmodified(): void
    {
        $original = [3, 1, 4, 2];
        $backup = $original; // Keep a copy

        $sorted = Sort::list($original, Order::Ascending);

        // Original should be unchanged
        self::assertSame($backup, $original);
        self::assertSame([1, 2, 3, 4], $sorted);
        self::assertNotSame($original, $sorted);
    }

    #[Test]
    public function associativeSortOriginalArrayUnmodified(): void
    {
        $original = ['c' => 3, 'a' => 1, 'b' => 2];
        $backup = $original; // Keep a copy

        $sorted = Sort::associative($original, Order::Ascending);

        // Original should be unchanged
        self::assertSame($backup, $original);
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $sorted);
        self::assertNotSame($original, $sorted);
    }

    #[Test]
    public function keySortOriginalArrayUnmodified(): void
    {
        $original = ['c' => 3, 'a' => 1, 'b' => 2];
        $backup = $original; // Keep a copy

        $sorted = Sort::key($original, Order::Ascending);

        // Original should be unchanged
        self::assertSame($backup, $original);
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $sorted);
        self::assertNotSame($original, $sorted);
    }

    #[Test]
    public function listSortWithCallableComparator(): void
    {
        $input = [5, 2, 8, 1, 9];
        // Custom comparator that sorts by absolute distance from 5
        $comparator = static fn(int $a, int $b): int => \abs($a - 5) <=> \abs($b - 5);

        $result = Sort::list($input, $comparator);
        self::assertSame([5, 2, 8, 1, 9], $result); // 5 is closest to 5, then 2 and 8 (equal distance), then 1 and 9
    }

    #[Test]
    public function associativeSortWithCallableComparator(): void
    {
        $input = ['a' => 5, 'b' => 2, 'c' => 8, 'd' => 1];
        // Sort by value in descending order
        $comparator = static fn(int $a, int $b): int => $b <=> $a;

        $result = Sort::associative($input, $comparator);
        self::assertSame(['c' => 8, 'a' => 5, 'b' => 2, 'd' => 1], $result);
    }

    #[Test]
    public function keySortWithCallableComparator(): void
    {
        $input = ['short' => 1, 'very_long_key' => 2, 'medium' => 3];
        // Sort keys by length
        $comparator = static fn(string $a, string $b): int => \strlen($a) <=> \strlen($b);

        $result = Sort::key($input, $comparator);
        self::assertSame(['short' => 1, 'medium' => 3, 'very_long_key' => 2], $result);
    }

    #[Test]
    public function listSortConvertsIteratorToArray(): void
    {
        $iterator = new \ArrayIterator([3, 1, 2]);
        $result = Sort::list($iterator, Order::Ascending);

        self::assertSame([1, 2, 3], $result);
        // Verify iterator was consumed
        self::assertFalse($iterator->valid());
    }

    #[Test]
    public function associativeSortConvertsIteratorToArray(): void
    {
        $iterator = new \ArrayIterator(['c' => 3, 'a' => 1, 'b' => 2]);
        $result = Sort::associative($iterator, Order::Ascending);

        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    #[Test]
    public function keySortConvertsIteratorToArray(): void
    {
        $iterator = new \ArrayIterator(['c' => 3, 'a' => 1, 'b' => 2]);
        $result = Sort::key($iterator, Order::Ascending);

        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    #[Test]
    public function listSortPreservesNumericIndexing(): void
    {
        $input = [10 => 'c', 5 => 'a', 15 => 'b'];
        $result = Sort::list($input, Order::Ascending);

        // list() should re-index from 0
        self::assertSame([0 => 'a', 1 => 'b', 2 => 'c'], $result);
    }

    #[Test]
    public function listSortWithSingleElement(): void
    {
        $input = ['only'];
        $result = Sort::list($input, Order::Descending, Comparison::String);

        self::assertSame(['only'], $result);
    }

    #[Test]
    public function associativeSortWithSingleElement(): void
    {
        $input = ['key' => 'value'];
        $result = Sort::associative($input, Order::Descending, Comparison::String);

        self::assertSame(['key' => 'value'], $result);
    }

    #[Test]
    public function keySortWithSingleElement(): void
    {
        $input = ['key' => 'value'];
        $result = Sort::key($input, Order::Descending, Comparison::String);

        self::assertSame(['key' => 'value'], $result);
    }
}
