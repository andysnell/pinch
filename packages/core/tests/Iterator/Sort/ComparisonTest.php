<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Iterator\Sort;

use PhoneBurner\Pinch\Iterator\Sort\Comparison;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ComparisonTest extends TestCase
{
    #[Test]
    #[DataProvider('providesComparisonValues')]
    public function comparisonEnumHasCorrectValues(Comparison $comparison, int $expected_value): void
    {
        self::assertSame($expected_value, $comparison->value);
    }

    /**
     * @return \Generator<array{Comparison, int}>
     */
    public static function providesComparisonValues(): \Generator
    {
        yield 'Regular' => [Comparison::Regular, \SORT_REGULAR];
        yield 'String' => [Comparison::String, \SORT_STRING];
        yield 'StringCaseInsensitive' => [Comparison::StringCaseInsensitive, \SORT_STRING | \SORT_FLAG_CASE];
        yield 'Natural' => [Comparison::Natural, \SORT_NATURAL];
        yield 'NaturalCaseInsensitive' => [Comparison::NaturalCaseInsensitive, \SORT_NATURAL | \SORT_FLAG_CASE];
        yield 'Numeric' => [Comparison::Numeric, \SORT_NUMERIC];
        yield 'Locale' => [Comparison::Locale, \SORT_LOCALE_STRING];
    }

    #[Test]
    public function comparisonEnumFromIntegerValues(): void
    {
        self::assertSame(Comparison::Regular, Comparison::from(\SORT_REGULAR));
        self::assertSame(Comparison::String, Comparison::from(\SORT_STRING));
        self::assertSame(Comparison::Natural, Comparison::from(\SORT_NATURAL));
        self::assertSame(Comparison::Numeric, Comparison::from(\SORT_NUMERIC));
        self::assertSame(Comparison::Locale, Comparison::from(\SORT_LOCALE_STRING));
        self::assertSame(Comparison::StringCaseInsensitive, Comparison::from(\SORT_STRING | \SORT_FLAG_CASE));
        self::assertSame(Comparison::NaturalCaseInsensitive, Comparison::from(\SORT_NATURAL | \SORT_FLAG_CASE));
    }

    #[Test]
    public function comparisonEnumCases(): void
    {
        $expected_cases = [
            'Regular',
            'String',
            'StringCaseInsensitive',
            'Natural',
            'NaturalCaseInsensitive',
            'Numeric',
            'Locale',
        ];

        $actual_case_names = \array_map(static fn(Comparison $case): string => $case->name, Comparison::cases());
        self::assertSame($expected_cases, $actual_case_names);
    }

    #[Test]
    public function comparisonEnumTryFromWithInvalidValue(): void
    {
        $invalid_value = 999999; // Value that doesn't correspond to any case
        self::assertNull(Comparison::tryFrom($invalid_value));
    }
}
