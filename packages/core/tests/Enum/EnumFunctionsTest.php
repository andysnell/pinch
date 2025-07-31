<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Enum;

use PhoneBurner\Pinch\Tests\Fixtures\Attributes\MockClassConstantAttribute;
use PhoneBurner\Pinch\Tests\Fixtures\Attributes\MockEnumAttribute;
use PhoneBurner\Pinch\Tests\Fixtures\Attributes\MockRepeatableEnumAttribute;
use PhoneBurner\Pinch\Tests\Fixtures\EnumWithAttributes;
use PhoneBurner\Pinch\Tests\Fixtures\EnumWithMultipleAttributes;
use PhoneBurner\Pinch\Tests\Fixtures\EnumWithoutAttributes;
use PhoneBurner\Pinch\Tests\Fixtures\IntBackedEnum;
use PhoneBurner\Pinch\Tests\Fixtures\StoplightState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Enum\case_attr_fetch;
use function PhoneBurner\Pinch\Enum\case_attr_find;
use function PhoneBurner\Pinch\Enum\case_attr_first;
use function PhoneBurner\Pinch\Enum\enum_values;

final class EnumFunctionsTest extends TestCase
{
    #[Test]
    public function enumValuesWithSingleIntEnum(): void
    {
        $result = enum_values(IntBackedEnum::Bar);
        self::assertSame([2], $result);
    }

    #[Test]
    public function enumValuesWithMultipleIntEnums(): void
    {
        $result = enum_values(IntBackedEnum::Foo, IntBackedEnum::Baz);
        self::assertSame([1, 3], $result);
    }

    #[Test]
    public function enumValuesWithSingleStringEnum(): void
    {
        $result = enum_values(StoplightState::Red);
        self::assertSame(['red'], $result);
    }

    #[Test]
    public function enumValuesWithMultipleStringEnums(): void
    {
        $result = enum_values(StoplightState::Yellow, StoplightState::Green);
        self::assertSame(['yellow', 'green'], $result);
    }

    #[Test]
    public function enumValuesWithMixedEnums(): void
    {
        $result = enum_values(IntBackedEnum::Foo, StoplightState::Red, IntBackedEnum::Baz);
        self::assertSame([1, 'red', 3], $result);
    }

    #[Test]
    public function enumValuesWithNoEnums(): void
    {
        $result = enum_values();
        self::assertSame([], $result);
    }

    #[Test]
    #[DataProvider('findCasesProvider')]
    public function enumCaseAttrFindReturnsExpectedAttributes(
        \BackedEnum $enum_case,
        string|null $attribute_class,
        array $expected_attributes,
    ): void {
        self::assertTrue($attribute_class === null || \class_exists($attribute_class));
        $actual = case_attr_find($enum_case, $attribute_class);
        self::assertEquals($expected_attributes, $actual);
    }

    public static function findCasesProvider(): \Generator
    {
        yield 'no attributes defined, no specific class' => [
            EnumWithoutAttributes::CaseA,
            null,
            [],
        ];

        yield 'no attributes defined, specific class' => [
            EnumWithoutAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            [],
        ];

        yield 'one attribute defined, no specific class' => [
            EnumWithAttributes::CaseA,
            null,
            [new MockRepeatableEnumAttribute('Case A Value')],
        ];

        yield 'one attribute defined, matching specific class' => [
            EnumWithAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            [new MockRepeatableEnumAttribute('Case A Value')],
        ];

        yield 'one attribute defined, non-matching specific class' => [
            EnumWithAttributes::CaseA,
            MockClassConstantAttribute::class,
            [],
        ];

        $expected_multiple = [
            new MockRepeatableEnumAttribute('Multi A 1'),
            new MockClassConstantAttribute('Multi A 2'),
            new MockRepeatableEnumAttribute('Multi A 3'),
        ];
        yield 'multiple attributes defined, no specific class' => [
            EnumWithMultipleAttributes::CaseA,
            null,
            $expected_multiple,
        ];

        yield 'multiple attributes defined, matching specific class (TestAttribute)' => [
            EnumWithMultipleAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            [
                new MockRepeatableEnumAttribute('Multi A 1'),
                new MockRepeatableEnumAttribute('Multi A 3'),
            ],
        ];

        yield 'multiple attributes defined, matching specific class (TestAttributeB)' => [
            EnumWithMultipleAttributes::CaseA,
            MockClassConstantAttribute::class,
            [new MockClassConstantAttribute('Multi A 2')],
        ];

        yield 'multiple attributes defined, non-matching specific class (TestAttributeC)' => [
            EnumWithMultipleAttributes::CaseA,
            MockEnumAttribute::class,
            [],
        ];
    }

    #[Test]
    #[DataProvider('firstCasesProvider')]
    public function enumCaseAttrFirstReturnsExpectedAttributeOrNull(
        \BackedEnum $enum_case,
        string|null $attribute_class,
        object|null $expected_attribute,
    ): void {
        self::assertTrue($attribute_class === null || \class_exists($attribute_class));
        $actual = case_attr_first($enum_case, $attribute_class);
        self::assertEquals($expected_attribute, $actual);
    }

    public static function firstCasesProvider(): \Generator
    {
        yield 'no attributes defined, no specific class' => [
            EnumWithoutAttributes::CaseA,
            null,
            null,
        ];

        yield 'no attributes defined, specific class' => [
            EnumWithoutAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            null,
        ];

        yield 'one attribute defined, no specific class' => [
            EnumWithAttributes::CaseA,
            null,
            new MockRepeatableEnumAttribute('Case A Value'),
        ];

        yield 'one attribute defined, matching specific class' => [
            EnumWithAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            new MockRepeatableEnumAttribute('Case A Value'),
        ];

        yield 'one attribute defined, non-matching specific class' => [
            EnumWithAttributes::CaseA,
            MockClassConstantAttribute::class,
            null,
        ];

        yield 'multiple attributes defined, no specific class' => [
            EnumWithMultipleAttributes::CaseA,
            null,
            new MockRepeatableEnumAttribute('Multi A 1'),
        ];

        yield 'multiple attributes defined, matching specific class (TestAttribute)' => [
            EnumWithMultipleAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            new MockRepeatableEnumAttribute('Multi A 1'),
        ];

        yield 'multiple attributes defined, matching specific class (TestAttributeB)' => [
            EnumWithMultipleAttributes::CaseA,
            MockClassConstantAttribute::class,
            new MockClassConstantAttribute('Multi A 2'),
        ];

        yield 'multiple attributes defined, non-matching specific class (TestAttributeC)' => [
            EnumWithMultipleAttributes::CaseA,
            MockEnumAttribute::class,
            null,
        ];
    }

    #[Test]
    #[DataProvider('fetchSuccessCasesProvider')]
    public function enumCaseAttrFetchReturnsExpectedAttribute(
        \BackedEnum $enum_case,
        string $attribute_class,
        object $expected_attribute,
    ): void {
        self::assertTrue(\class_exists($attribute_class));
        $actual = case_attr_fetch($enum_case, $attribute_class);
        self::assertEquals($expected_attribute, $actual);
    }

    public static function fetchSuccessCasesProvider(): \Generator
    {
        yield 'one attribute defined, matching specific class' => [
            EnumWithAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            new MockRepeatableEnumAttribute('Case A Value'),
        ];

        yield 'multiple attributes defined, matching specific class (TestAttribute)' => [
            EnumWithMultipleAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            new MockRepeatableEnumAttribute('Multi A 1'),
        ];

        yield 'multiple attributes defined, matching specific class (TestAttributeB)' => [
            EnumWithMultipleAttributes::CaseA,
            MockClassConstantAttribute::class,
            new MockClassConstantAttribute('Multi A 2'),
        ];
    }

    #[Test]
    #[DataProvider('fetchThrowsExceptionCasesProvider')]
    public function enumCaseAttrFetchThrowsExceptionWhenAttributeNotFound(
        \BackedEnum $enum_case,
        string $attribute_class,
        string $expected_exception_message,
    ): void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expected_exception_message);
        /** @phpstan-ignore argument.type, argument.templateType */
        case_attr_fetch($enum_case, $attribute_class);
    }

    public static function fetchThrowsExceptionCasesProvider(): \Generator
    {
        yield 'no attributes defined, specific class' => [
            EnumWithoutAttributes::CaseA,
            MockRepeatableEnumAttribute::class,
            \sprintf("Attribute %s Not Found for Enum Case %s::CaseA", MockRepeatableEnumAttribute::class, EnumWithoutAttributes::class),
        ];

        yield 'one attribute defined, non-matching specific class' => [
            EnumWithAttributes::CaseA,
            MockClassConstantAttribute::class,
            \sprintf("Attribute %s Not Found for Enum Case %s::CaseA", MockClassConstantAttribute::class, EnumWithAttributes::class),
        ];

        yield 'multiple attributes defined, non-matching specific class (TestAttributeC)' => [
            EnumWithMultipleAttributes::CaseA,
            MockEnumAttribute::class,
            \sprintf("Attribute %s Not Found for Enum Case %s::CaseA", MockEnumAttribute::class, EnumWithMultipleAttributes::class),
        ];
    }
}
