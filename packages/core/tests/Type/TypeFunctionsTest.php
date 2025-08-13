<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Type;

use Carbon\CarbonImmutable;
use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Tests\Fixtures\IntBackedEnum;
use PhoneBurner\Pinch\Tests\Fixtures\StoplightState;
use PhoneBurner\Pinch\Time\Standards\AnsiSql;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Type\cast_nullable_bool;
use function PhoneBurner\Pinch\Type\cast_nullable_datetime;
use function PhoneBurner\Pinch\Type\cast_nullable_float;
use function PhoneBurner\Pinch\Type\cast_nullable_int;
use function PhoneBurner\Pinch\Type\cast_nullable_nonempty_array;
use function PhoneBurner\Pinch\Type\cast_nullable_nonempty_bool;
use function PhoneBurner\Pinch\Type\cast_nullable_nonempty_float;
use function PhoneBurner\Pinch\Type\cast_nullable_nonempty_int;
use function PhoneBurner\Pinch\Type\cast_nullable_nonempty_string;
use function PhoneBurner\Pinch\Type\cast_nullable_string;
use function PhoneBurner\Pinch\Type\get_debug_value;
use function PhoneBurner\Pinch\Type\is_accessible;
use function PhoneBurner\Pinch\Type\is_arrayable;
use function PhoneBurner\Pinch\Type\is_castable_to_string;
use function PhoneBurner\Pinch\Type\is_class;
use function PhoneBurner\Pinch\Type\is_class_string;
use function PhoneBurner\Pinch\Type\is_class_string_of;
use function PhoneBurner\Pinch\Type\is_negative_int;
use function PhoneBurner\Pinch\Type\is_non_negative_int;
use function PhoneBurner\Pinch\Type\is_non_positive_int;
use function PhoneBurner\Pinch\Type\is_non_zero_int;
use function PhoneBurner\Pinch\Type\is_nonempty_array;
use function PhoneBurner\Pinch\Type\is_nonempty_list;
use function PhoneBurner\Pinch\Type\is_nonempty_string;
use function PhoneBurner\Pinch\Type\is_positive_int;
use function PhoneBurner\Pinch\Type\is_stream_resource;
use function PhoneBurner\Pinch\Type\is_stringable;
use function PhoneBurner\Pinch\Type\narrow;
use function PhoneBurner\Pinch\Type\narrow_accessible;
use function PhoneBurner\Pinch\Type\narrow_array;
use function PhoneBurner\Pinch\Type\narrow_bool;
use function PhoneBurner\Pinch\Type\narrow_callable;
use function PhoneBurner\Pinch\Type\narrow_class_string;
use function PhoneBurner\Pinch\Type\narrow_float;
use function PhoneBurner\Pinch\Type\narrow_int;
use function PhoneBurner\Pinch\Type\narrow_iterable;
use function PhoneBurner\Pinch\Type\narrow_nonempty_string;
use function PhoneBurner\Pinch\Type\narrow_nullable_accessible;
use function PhoneBurner\Pinch\Type\narrow_nullable_array;
use function PhoneBurner\Pinch\Type\narrow_nullable_bool;
use function PhoneBurner\Pinch\Type\narrow_nullable_callable;
use function PhoneBurner\Pinch\Type\narrow_nullable_class_string;
use function PhoneBurner\Pinch\Type\narrow_nullable_float;
use function PhoneBurner\Pinch\Type\narrow_nullable_int;
use function PhoneBurner\Pinch\Type\narrow_nullable_iterable;
use function PhoneBurner\Pinch\Type\narrow_nullable_nonempty_string;
use function PhoneBurner\Pinch\Type\narrow_nullable_positive_int;
use function PhoneBurner\Pinch\Type\narrow_nullable_resource;
use function PhoneBurner\Pinch\Type\narrow_nullable_string;
use function PhoneBurner\Pinch\Type\narrow_positive_int;
use function PhoneBurner\Pinch\Type\narrow_resource;
use function PhoneBurner\Pinch\Type\narrow_string;

#[CoversFunction('PhoneBurner\Pinch\Type\get_debug_value')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_accessible')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_arrayable')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_castable_to_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_class')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_class_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_class_string_of')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_negative_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_nonempty_array')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_nonempty_list')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_nonempty_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_non_negative_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_non_positive_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_non_zero_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_positive_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_stream_resource')]
#[CoversFunction('PhoneBurner\Pinch\Type\is_stringable')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_accessible')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_array')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_bool')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_callable')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_class_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_float')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_iterable')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nonempty_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_accessible')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_array')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_bool')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_callable')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_class_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_float')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_iterable')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_nonempty_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_positive_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_resource')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_nullable_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_positive_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_resource')]
#[CoversFunction('PhoneBurner\Pinch\Type\narrow_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\cast_nullable_string')]
#[CoversFunction('PhoneBurner\Pinch\Type\cast_nullable_int')]
#[CoversFunction('PhoneBurner\Pinch\Type\cast_nullable_float')]
#[CoversFunction('PhoneBurner\Pinch\Type\cast_nullable_bool')]
#[CoversFunction('PhoneBurner\Pinch\Type\cast_nullable_datetime')]
final class TypeFunctionsTest extends TestCase
{
    #[Test]
    public function isStringableReturnsTrueForStringables(): void
    {
        self::assertTrue(is_stringable('string'));

        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };

        self::assertTrue(is_stringable($stringable));
    }

    #[Test]
    public function isStringableReturnsFalseForNonStringables(): void
    {
        self::assertFalse(is_stringable(42));
        self::assertFalse(is_stringable(true));
        self::assertFalse(is_stringable([]));
        self::assertFalse(is_stringable(new \stdClass()));
        self::assertFalse(is_stringable(null));
    }

    #[Test]
    public function isCastableToStringReturnsTrueForCastableTypes(): void
    {
        self::assertTrue(is_castable_to_string('string'));
        self::assertTrue(is_castable_to_string(42));
        self::assertTrue(is_castable_to_string(3.14));
        self::assertTrue(is_castable_to_string(true));
        self::assertTrue(is_castable_to_string(false));
        self::assertTrue(is_castable_to_string(null));

        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };
        self::assertTrue(is_castable_to_string($stringable));
    }

    #[Test]
    public function isCastableToStringReturnsFalseForNonCastableTypes(): void
    {
        self::assertFalse(is_castable_to_string([]));
        self::assertFalse(is_castable_to_string(new \stdClass()));
        self::assertFalse(is_castable_to_string(\fopen('php://memory', 'r+b')));
    }

    #[Test]
    public function isClassStringReturnsTrueForClassStrings(): void
    {
        self::assertTrue(is_class_string(\stdClass::class));
        self::assertTrue(is_class_string(\DateTimeImmutable::class));
        self::assertTrue(is_class_string(\Stringable::class)); // interface
        self::assertTrue(is_class_string(\ArrayAccess::class)); // interface
    }

    #[Test]
    public function isClassStringReturnsFalseForNonClassStrings(): void
    {
        self::assertFalse(is_class_string('not_a_class'));
        self::assertFalse(is_class_string(''));
        self::assertFalse(is_class_string(42));
        self::assertFalse(is_class_string(null));
        self::assertFalse(is_class_string(new \stdClass()));
    }

    #[Test]
    public function isClassStringOfReturnsTrueForMatchingClassString(): void
    {
        self::assertTrue(is_class_string_of(\stdClass::class, \stdClass::class));
        self::assertTrue(is_class_string_of(\DateTimeImmutable::class, \DateTimeImmutable::class));
        self::assertTrue(is_class_string_of(\DateTimeInterface::class, \DateTimeImmutable::class));
    }

    #[Test]
    public function isClassStringOfReturnsFalseForNonMatchingTypes(): void
    {
        self::assertFalse(is_class_string_of(\stdClass::class, \DateTimeImmutable::class));
        self::assertFalse(is_class_string_of(\stdClass::class, 'not_a_class'));
        self::assertFalse(is_class_string_of(\stdClass::class, 42));
        self::assertFalse(is_class_string_of(\stdClass::class, null));
    }

    #[Test]
    public function isClassReturnsTrueForObjectsAndClassStrings(): void
    {
        self::assertTrue(is_class(new \stdClass()));
        self::assertTrue(is_class(new \DateTimeImmutable()));
        self::assertTrue(is_class(\stdClass::class));
        self::assertTrue(is_class(\DateTimeImmutable::class));
    }

    #[Test]
    public function isClassReturnsFalseForNonClassTypes(): void
    {
        self::assertFalse(is_class('not_a_class'));
        self::assertFalse(is_class(42));
        self::assertFalse(is_class(null));
        self::assertFalse(is_class([]));
        self::assertFalse(is_class('string'));
    }

    #[Test]
    public function isNonEmptyArrayReturnsTrueForNonEmptyArrays(): void
    {
        self::assertTrue(is_nonempty_array([1, 2, 3]));
        self::assertTrue(is_nonempty_array(['key' => 'value']));
        self::assertTrue(is_nonempty_array([0]));
    }

    #[Test]
    public function isNonEmptyArrayReturnsFalseForEmptyArraysAndNonArrays(): void
    {
        self::assertFalse(is_nonempty_array([]));
        self::assertFalse(is_nonempty_array('string'));
        self::assertFalse(is_nonempty_array(42));
        self::assertFalse(is_nonempty_array(null));
        self::assertFalse(is_nonempty_array(new \stdClass()));
    }

    #[Test]
    public function isNonEmptyListReturnsTrueForNonEmptyLists(): void
    {
        self::assertTrue(is_nonempty_list([1, 2, 3]));
        self::assertTrue(is_nonempty_list(['a', 'b', 'c']));
        self::assertTrue(is_nonempty_list([0]));
    }

    #[Test]
    public function isNonEmptyListReturnsFalseForEmptyListsAndNonLists(): void
    {
        self::assertFalse(is_nonempty_list([]));
        self::assertFalse(is_nonempty_list(['key' => 'value'])); // associative array
        self::assertFalse(is_nonempty_list('string'));
        self::assertFalse(is_nonempty_list(42));
        self::assertFalse(is_nonempty_list(null));
    }

    #[Test]
    public function isPositiveIntReturnsTrueForPositiveInts(): void
    {
        self::assertTrue(is_positive_int(1));
        self::assertTrue(is_positive_int(42));
        self::assertTrue(is_positive_int(\PHP_INT_MAX));
    }

    #[Test]
    public function isPositiveIntReturnsFalseForNonPositiveInts(): void
    {
        self::assertFalse(is_positive_int(0));
        self::assertFalse(is_positive_int(-1));
        self::assertFalse(is_positive_int(-42));
        self::assertFalse(is_positive_int(3.14)); // float
        self::assertFalse(is_positive_int('42')); // string
        self::assertFalse(is_positive_int(null));
    }

    #[Test]
    public function isNegativeIntReturnsTrueForNegativeInts(): void
    {
        self::assertTrue(is_negative_int(-1));
        self::assertTrue(is_negative_int(-42));
        self::assertTrue(is_negative_int(\PHP_INT_MIN));
    }

    #[Test]
    public function isNegativeIntReturnsFalseForNonNegativeInts(): void
    {
        self::assertFalse(is_negative_int(0));
        self::assertFalse(is_negative_int(1));
        self::assertFalse(is_negative_int(42));
        self::assertFalse(is_negative_int(-3.14)); // float
        self::assertFalse(is_negative_int('-42')); // string
        self::assertFalse(is_negative_int(null));
    }

    #[Test]
    public function isNonPositiveIntReturnsTrueForNonPositiveInts(): void
    {
        self::assertTrue(is_non_positive_int(0));
        self::assertTrue(is_non_positive_int(-1));
        self::assertTrue(is_non_positive_int(-42));
        self::assertTrue(is_non_positive_int(\PHP_INT_MIN));
    }

    #[Test]
    public function isNonPositiveIntReturnsFalseForPositiveIntsAndNonInts(): void
    {
        self::assertFalse(is_non_positive_int(1));
        self::assertFalse(is_non_positive_int(42));
        self::assertFalse(is_non_positive_int(-3.14)); // float
        self::assertFalse(is_non_positive_int('0')); // string
        self::assertFalse(is_non_positive_int(null));
    }

    #[Test]
    public function isNonNegativeIntReturnsTrueForNonNegativeInts(): void
    {
        self::assertTrue(is_non_negative_int(0));
        self::assertTrue(is_non_negative_int(1));
        self::assertTrue(is_non_negative_int(42));
        self::assertTrue(is_non_negative_int(\PHP_INT_MAX));
    }

    #[Test]
    public function isNonNegativeIntReturnsFalseForNegativeIntsAndNonInts(): void
    {
        self::assertFalse(is_non_negative_int(-1));
        self::assertFalse(is_non_negative_int(-42));
        self::assertFalse(is_non_negative_int(3.14)); // float
        self::assertFalse(is_non_negative_int('0')); // string
        self::assertFalse(is_non_negative_int(null));
    }

    #[Test]
    public function isNonZeroIntReturnsTrueForNonZeroInts(): void
    {
        self::assertTrue(is_non_zero_int(1));
        self::assertTrue(is_non_zero_int(-1));
        self::assertTrue(is_non_zero_int(42));
        self::assertTrue(is_non_zero_int(-42));
        self::assertTrue(is_non_zero_int(\PHP_INT_MAX));
        self::assertTrue(is_non_zero_int(\PHP_INT_MIN));
    }

    #[Test]
    public function isNonZeroIntReturnsFalseForZeroAndNonInts(): void
    {
        self::assertFalse(is_non_zero_int(0));
        self::assertFalse(is_non_zero_int(3.14)); // float
        self::assertFalse(is_non_zero_int('42')); // string
        self::assertFalse(is_non_zero_int(null));
    }

    #[Test]
    public function isStreamResourceReturnsTrueForStreamResources(): void
    {
        $resource = \fopen('php://memory', 'r+b');
        self::assertTrue(is_stream_resource($resource));
        \fclose($resource);
    }

    #[Test]
    public function isStreamResourceReturnsFalseForNonStreamResources(): void
    {
        self::assertFalse(is_stream_resource('string'));
        self::assertFalse(is_stream_resource(42));
        self::assertFalse(is_stream_resource(null));
        self::assertFalse(is_stream_resource([]));
        self::assertFalse(is_stream_resource(new \stdClass()));
    }

    #[Test]
    public function isNonEmptyStringReturnsTrueForNonEmptyStrings(): void
    {
        self::assertTrue(is_nonempty_string('hello'));
        self::assertTrue(is_nonempty_string('0'));
        self::assertTrue(is_nonempty_string(' '));
    }

    #[Test]
    public function isNonEmptyStringReturnsFalseForEmptyStringsAndNonStrings(): void
    {
        self::assertFalse(is_nonempty_string(''));
        self::assertFalse(is_nonempty_string(42));
        self::assertFalse(is_nonempty_string(null));
        self::assertFalse(is_nonempty_string([]));
        self::assertFalse(is_nonempty_string(new \stdClass()));
    }

    #[Test]
    public function getDebugValueReturnsCorrectRepresentation(): void
    {
        self::assertSame('null', get_debug_value(null));
        self::assertSame('(bool)true', get_debug_value(true));
        self::assertSame('(bool)false', get_debug_value(false));
        self::assertSame('(int)42', get_debug_value(42));
        self::assertSame('(int)-42', get_debug_value(-42));
        self::assertSame('(float)3.14', get_debug_value(3.14));
        self::assertSame('(string)hello', get_debug_value('hello'));
        self::assertSame('(string)', get_debug_value(''));

        // Array should return print_r output
        $array = ['a', 'b'];
        self::assertSame(\print_r($array, true), get_debug_value($array));

        // Objects should return type name
        self::assertSame('stdClass', get_debug_value(new \stdClass()));

        $resource = \fopen('php://memory', 'rb');
        self::assertSame('resource (stream)', get_debug_value($resource));
        $resource && \fclose($resource);
    }

    #[Test]
    public function isAccessibleReturnsTrueForArrays(): void
    {
        self::assertTrue(is_accessible([]));
        self::assertTrue(is_accessible(['foo' => 'bar']));
    }

    #[Test]
    public function isAccessibleReturnsTrueForArrayAccess(): void
    {
        self::assertTrue(is_accessible(new \ArrayObject()));
    }

    #[Test]
    public function isAccessibleReturnsFalseForOtherTypes(): void
    {
        /** @var mixed $string */
        $string = 'string';
        /** @var mixed $number */
        $number = 42;
        $object = new \stdClass();

        self::assertFalse(is_accessible($string));
        self::assertFalse(is_accessible($number));
        /** @phpstan-ignore function.impossibleType (intentional defect for testing) */
        self::assertFalse(is_accessible($object));
    }

    #[Test]
    public function isArrayableReturnsTrueForArrays(): void
    {
        self::assertTrue(is_arrayable([]));
        self::assertTrue(is_arrayable(['foo' => 'bar']));
    }

    #[Test]
    public function isArrayableReturnsTrueForArrayable(): void
    {
        $arrayable = new class implements Arrayable {
            public function toArray(): array
            {
                return ['test' => 'value'];
            }
        };

        self::assertTrue(is_arrayable($arrayable));
    }

    #[Test]
    public function isArrayableReturnsTrueForTraversable(): void
    {
        self::assertTrue(is_arrayable(new \ArrayIterator([1, 2, 3])));
    }

    #[Test]
    public function narrowNullableStringReturnsStringForValidInput(): void
    {
        self::assertSame('test', narrow_nullable_string('test'));
    }

    #[Test]
    public function narrowNullableStringReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_string(null));
    }

    #[Test]
    public function narrowNullableStringThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_string(123);
    }

    #[Test]
    public function narrowNullableNonEmptyStringReturnsStringForValidInput(): void
    {
        self::assertSame('test', narrow_nullable_nonempty_string('test'));
    }

    #[Test]
    public function narrowNullableNonEmptyStringReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_nonempty_string(null));
    }

    #[Test]
    public function narrowNullableNonEmptyStringThrowsForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_nonempty_string('');
    }

    #[Test]
    public function narrowNullableIntReturnsIntForValidInput(): void
    {
        self::assertSame(42, narrow_nullable_int(42));
    }

    #[Test]
    public function narrowNullableIntReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_int(null));
    }

    #[Test]
    public function narrowNullableIntThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_int('not an int');
    }

    #[Test]
    public function narrowNullablePositiveIntReturnsIntForValidInput(): void
    {
        self::assertSame(5, narrow_nullable_positive_int(5));
    }

    #[Test]
    public function narrowNullablePositiveIntReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_positive_int(null));
    }

    #[Test]
    public function narrowNullablePositiveIntThrowsForZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_positive_int(0);
    }

    #[Test]
    public function narrowNullableFloatReturnsFloatForValidInput(): void
    {
        self::assertSame(3.14, narrow_nullable_float(3.14));
    }

    #[Test]
    public function narrowNullableFloatReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_float(null));
    }

    #[Test]
    public function narrowNullableFloatThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_float('not a float');
    }

    #[Test]
    public function narrowNullableBoolReturnsBoolForValidInput(): void
    {
        self::assertTrue(narrow_nullable_bool(true));
        self::assertFalse(narrow_nullable_bool(false));
    }

    #[Test]
    public function narrowNullableBoolReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_bool(null));
    }

    #[Test]
    public function narrowNullableBoolThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_bool('not a bool');
    }

    #[Test]
    public function narrowNullableArrayReturnsArrayForValidInput(): void
    {
        $array = ['a', 'b', 'c'];
        self::assertSame($array, narrow_nullable_array($array));
    }

    #[Test]
    public function narrowNullableArrayReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_array(null));
    }

    #[Test]
    public function narrowNullableArrayThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_array('not an array');
    }

    #[Test]
    public function narrowNullableIterableReturnsIterableForValidInput(): void
    {
        $array = ['a', 'b', 'c'];
        self::assertSame($array, narrow_nullable_iterable($array));

        $iterator = new \ArrayIterator($array);
        self::assertSame($iterator, narrow_nullable_iterable($iterator));
    }

    #[Test]
    public function narrowNullableIterableReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_iterable(null));
    }

    #[Test]
    public function narrowNullableIterableThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_iterable('not iterable');
    }

    #[Test]
    public function narrowNullableAccessibleReturnsAccessibleForValidInput(): void
    {
        $array = ['a', 'b', 'c'];
        self::assertSame($array, narrow_nullable_accessible($array));

        $array_object = new \ArrayObject($array);
        self::assertSame($array_object, narrow_nullable_accessible($array_object));
    }

    #[Test]
    public function narrowNullableAccessibleReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_accessible(null));
    }

    #[Test]
    public function narrowNullableAccessibleThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_accessible('not accessible');
    }

    #[Test]
    public function narrowNullableCallableReturnsCallableForValidInput(): void
    {
        $callable = fn(): string => 'test';
        self::assertSame($callable, narrow_nullable_callable($callable));
    }

    #[Test]
    public function narrowNullableCallableReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_callable(null));
    }

    #[Test]
    public function narrowNullableCallableThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_callable('not callable');
    }

    #[Test]
    public function narrowNullableResourceReturnsResourceForValidInput(): void
    {
        $resource = \fopen('php://memory', 'r+b');
        self::assertSame($resource, narrow_nullable_resource($resource));
        \fclose($resource);
    }

    #[Test]
    public function narrowNullableResourceReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_resource(null));
    }

    #[Test]
    public function narrowNullableResourceThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_resource('not a resource');
    }

    #[Test]
    public function narrowNullableClassStringReturnsClassStringForValidInput(): void
    {
        self::assertSame(\stdClass::class, narrow_nullable_class_string(null, \stdClass::class));
        self::assertSame(\DateTimeImmutable::class, narrow_nullable_class_string(\DateTimeImmutable::class, \DateTimeImmutable::class));
        self::assertSame(\DateTimeImmutable::class, narrow_nullable_class_string(\DateTimeInterface::class, \DateTimeImmutable::class));
    }

    #[Test]
    public function narrowNullableClassStringReturnsNullForNull(): void
    {
        self::assertNull(narrow_nullable_class_string(null, null));
        self::assertNull(narrow_nullable_class_string(\stdClass::class, null));
    }

    #[Test]
    public function narrowNullableClassStringThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_class_string(null, 'not_a_class');
    }

    #[Test]
    public function narrowNullableClassStringThrowsForWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nullable_class_string(\DateTimeImmutable::class, \stdClass::class);
    }

    // Tests for non-nullable narrow functions

    #[Test]
    public function narrowReturnsObjectForValidInput(): void
    {
        $object = new \stdClass();
        self::assertSame($object, narrow(\stdClass::class, $object));
    }

    #[Test]
    public function narrowThrowsForInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of stdClass, but got DateTimeImmutable');
        narrow(\stdClass::class, new \DateTimeImmutable());
    }

    #[Test]
    public function narrowStringReturnsStringForValidInput(): void
    {
        self::assertSame('test', narrow_string('test'));
    }

    #[Test]
    public function narrowStringThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_string(123);
    }

    #[Test]
    public function narrowNonEmptyStringReturnsStringForValidInput(): void
    {
        self::assertSame('test', narrow_nonempty_string('test'));
    }

    #[Test]
    public function narrowNonEmptyStringThrowsForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_nonempty_string('');
    }

    #[Test]
    public function narrowIntReturnsIntForValidInput(): void
    {
        self::assertSame(42, narrow_int(42));
    }

    #[Test]
    public function narrowIntThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_int('not an int');
    }

    #[Test]
    public function narrowPositiveIntReturnsIntForValidInput(): void
    {
        self::assertSame(5, narrow_positive_int(5));
    }

    #[Test]
    public function narrowPositiveIntThrowsForZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_positive_int(0);
    }

    #[Test]
    public function narrowFloatReturnsFloatForValidInput(): void
    {
        self::assertSame(3.14, narrow_float(3.14));
    }

    #[Test]
    public function narrowFloatThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_float('not a float');
    }

    #[Test]
    public function narrowBoolReturnsBoolForValidInput(): void
    {
        self::assertTrue(narrow_bool(true));
        self::assertFalse(narrow_bool(false));
    }

    #[Test]
    public function narrowBoolThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_bool('not a bool');
    }

    #[Test]
    public function narrowArrayReturnsArrayForValidInput(): void
    {
        $array = ['a', 'b', 'c'];
        self::assertSame($array, narrow_array($array));
    }

    #[Test]
    public function narrowArrayThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_array('not an array');
    }

    #[Test]
    public function narrowIterableReturnsIterableForValidInput(): void
    {
        $array = ['a', 'b', 'c'];
        self::assertSame($array, narrow_iterable($array));

        $iterator = new \ArrayIterator($array);
        self::assertSame($iterator, narrow_iterable($iterator));
    }

    #[Test]
    public function narrowIterableThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_iterable('not iterable');
    }

    #[Test]
    public function narrowAccessibleReturnsAccessibleForValidInput(): void
    {
        $array = ['a', 'b', 'c'];
        self::assertSame($array, narrow_accessible($array));

        $array_object = new \ArrayObject($array);
        self::assertSame($array_object, narrow_accessible($array_object));
    }

    #[Test]
    public function narrowAccessibleThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_accessible('not accessible');
    }

    #[Test]
    public function narrowCallableReturnsCallableForValidInput(): void
    {
        $callable = fn(): string => 'test';
        self::assertSame($callable, narrow_callable($callable));
    }

    #[Test]
    public function narrowCallableThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_callable('not callable');
    }

    #[Test]
    public function narrowResourceReturnsResourceForValidInput(): void
    {
        $resource = \fopen('php://memory', 'r+b');
        self::assertSame($resource, narrow_resource($resource));
        \fclose($resource);
    }

    #[Test]
    public function narrowResourceThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_resource('not a resource');
    }

    #[Test]
    public function narrowClassStringReturnsClassStringForValidInput(): void
    {
        self::assertSame(\stdClass::class, narrow_class_string(null, \stdClass::class));
        self::assertSame(\DateTimeImmutable::class, narrow_class_string(\DateTimeImmutable::class, \DateTimeImmutable::class));
        self::assertSame(\DateTimeImmutable::class, narrow_class_string(\DateTimeInterface::class, \DateTimeImmutable::class));
    }

    #[Test]
    public function narrowClassStringThrowsForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_class_string(null, 'not_a_class');
    }

    #[Test]
    public function narrowClassStringThrowsForWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        narrow_class_string(\DateTimeImmutable::class, \stdClass::class);
    }

    #[DataProvider('providesIntegerTestCases')]
    #[Test]
    public function integerReturnsExpectedValue(mixed $input, int|null $expected): void
    {
        self::assertSame($expected, cast_nullable_int($input));
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
        self::assertSame($expected, cast_nullable_float($input));
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
        self::assertSame($expected, cast_nullable_string($input));
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
        self::assertSame($expected, cast_nullable_bool($input));
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
        $datetime = cast_nullable_datetime($input);
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
    public function integerThrowsInvalidArgumentExceptionForInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for integer cast: array');
        cast_nullable_int([]);
    }

    #[Test]
    public function integerThrowsInvalidArgumentExceptionForObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for integer cast: stdClass');
        cast_nullable_int(new \stdClass());
    }

    #[Test]
    public function integerHandlesBackedEnum(): void
    {
        self::assertSame(2, cast_nullable_int(IntBackedEnum::Bar));
        self::assertSame(0, cast_nullable_int(StoplightState::Red));
    }

    #[Test]
    public function floatThrowsInvalidArgumentExceptionForInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for float cast: array');
        cast_nullable_float([]);
    }

    #[Test]
    public function floatThrowsInvalidArgumentExceptionForObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for float cast: stdClass');
        cast_nullable_float(new \stdClass());
    }

    #[Test]
    public function stringThrowsInvalidArgumentExceptionForInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for string cast: array');
        cast_nullable_string([]);
    }

    #[Test]
    public function stringThrowsInvalidArgumentExceptionForObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for string cast: stdClass');
        cast_nullable_string(new \stdClass());
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

        self::assertSame('test', cast_nullable_string($stringable));
    }

    #[DataProvider('providesNonemptyIntegerTestCases')]
    #[Test]
    public function castNullableNonemptyIntReturnsExpectedValue(mixed $input, int|null $expected): void
    {
        self::assertSame($expected, cast_nullable_nonempty_int($input));
    }

    public static function providesNonemptyIntegerTestCases(): \Generator
    {
        yield [0, null];
        yield [1, 1];
        yield [-1, -1];
        yield [1.4433, 1];
        yield [\PHP_INT_MAX, \PHP_INT_MAX];
        yield [\PHP_INT_MIN, \PHP_INT_MIN];
        yield ['432', 432];
        yield ["hello, world", null];
        yield ['0', null];
        yield ['0.0', null];
        yield [true, 1];
        yield [false, null];
        yield [null, null];
    }

    #[DataProvider('providesNonemptyFloatTestCases')]
    #[Test]
    public function castNullableNonemptyFloatReturnsExpectedValue(mixed $input, float|null $expected): void
    {
        self::assertSame($expected, cast_nullable_nonempty_float($input));
    }

    public static function providesNonemptyFloatTestCases(): \Generator
    {
        yield [0, null];
        yield [0.0, null];
        yield [1, 1.0];
        yield [-1, -1.0];
        yield [1.4433, 1.4433];
        yield [\PHP_INT_MAX, (float)\PHP_INT_MAX];
        yield [\PHP_INT_MIN, (float)\PHP_INT_MIN];
        yield ['432', 432.0];
        yield ["hello, world", null];
        yield ['0', null];
        yield ['0.0', null];
        yield [true, 1.0];
        yield [false, null];
        yield [null, null];
    }

    #[DataProvider('providesNonemptyStringTestCases')]
    #[Test]
    public function castNullableNonemptyStringReturnsExpectedValue(mixed $input, string|null $expected): void
    {
        self::assertSame($expected, cast_nullable_nonempty_string($input));
    }

    public static function providesNonemptyStringTestCases(): \Generator
    {
        yield [0, null];
        yield [0.0, null];
        yield [1, '1'];
        yield [-1, '-1'];
        yield [1.4433, '1.4433'];
        yield [\PHP_INT_MAX, (string)\PHP_INT_MAX];
        yield [\PHP_INT_MIN, (string)\PHP_INT_MIN];
        yield ['432', '432'];
        yield ["hello, world", "hello, world"];
        yield ['0', null];
        yield ['0.0', '0.0'];
        yield [true, '1'];
        yield [false, null];
        yield [null, null];
    }

    #[DataProvider('providesNonemptyBooleanTestCases')]
    #[Test]
    public function castNullableNonemptyBoolReturnsExpectedValue(mixed $input, bool|null $expected): void
    {
        self::assertSame($expected, cast_nullable_nonempty_bool($input));
    }

    public static function providesNonemptyBooleanTestCases(): \Generator
    {
        yield [0, null];
        yield [1, true];
        yield [-1, true];
        yield [1.4433, true];
        yield [\PHP_INT_MAX, true];
        yield [\PHP_INT_MIN, true];
        yield ['432', true];
        yield ["hello, world", true];
        yield ['0', null];
        yield ['0.0', true];
        yield [true, true];
        yield [false, null];
        yield [null, null];
        yield [[], null];
        yield [[1,2,3], true];
        yield [['foo' => 'bar'], true];
        yield [new \stdClass(), true];
    }

    /**
     * @param array<mixed>|null $input
     * @param array<mixed>|null $expected
     */
    #[DataProvider('providesNonemptyArrayTestCases')]
    #[Test]
    public function castNullableNonemptyArrayReturnsExpectedValue(array|null $input, array|null $expected): void
    {
        self::assertSame($expected, cast_nullable_nonempty_array($input));
    }

    /**
     * @return \Generator<array{0: array<mixed>|null, 1: array<mixed>|null}>
     */
    public static function providesNonemptyArrayTestCases(): \Generator
    {
        yield [null, null];
        yield [[], null];
        yield [[1,2,3], [1,2,3]];
        yield [['foo' => 'bar'], ['foo' => 'bar']];
    }

    #[Test]
    public function castNullableNonemptyIntThrowsInvalidArgumentExceptionForObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for integer cast: stdClass');
        cast_nullable_nonempty_int(new \stdClass());
    }

    #[Test]
    public function castNullableNonemptyIntThrowsInvalidArgumentExceptionForArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for integer cast: array');
        cast_nullable_nonempty_int([]);
    }

    #[Test]
    public function castNullableNonemptyFloatThrowsInvalidArgumentExceptionForObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for float cast: stdClass');
        cast_nullable_nonempty_float(new \stdClass());
    }

    #[Test]
    public function castNullableNonemptyFloatThrowsInvalidArgumentExceptionForArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for float cast: array');
        cast_nullable_nonempty_float([]);
    }

    #[Test]
    public function castNullableNonemptyStringThrowsInvalidArgumentExceptionForObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for string cast: stdClass');
        cast_nullable_nonempty_string(new \stdClass());
    }

    #[Test]
    public function castNullableNonemptyStringThrowsInvalidArgumentExceptionForArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type for string cast: array');
        cast_nullable_nonempty_string([]);
    }
}
