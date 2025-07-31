<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math\Statistics;

use PhoneBurner\Pinch\Math\Statistics\FiveNumberSummary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FiveNumberSummaryTest extends TestCase
{
    #[Test]
    public function constructorSortsValuesAndSetsCount(): void
    {
        $values = [5, 1, 3, 4, 2];
        $summary = new FiveNumberSummary($values);

        self::assertSame(5, $summary->count);
        self::assertSame(1, $summary->min);
        self::assertSame(5, $summary->max);
    }

    #[Test]
    public function constructorThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Cannot calculate percentiles from a empty list.');

        new FiveNumberSummary([]);
    }

    #[Test]
    public function propertyHooksProvideCorrectValues(): void
    {
        $values = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $summary = new FiveNumberSummary($values);

        self::assertSame(1, $summary->min);
        self::assertSame(10, $summary->max);
        self::assertSame(3.25, $summary->first_quartile);
        self::assertSame(5.5, $summary->median);
        self::assertSame(7.75, $summary->third_quartile);
    }

    #[Test]
    public function singleValueDistribution(): void
    {
        $summary = new FiveNumberSummary([42]);

        self::assertSame(1, $summary->count);
        self::assertSame(42, $summary->min);
        self::assertSame(42, $summary->max);
        self::assertSame(42, $summary->first_quartile);
        self::assertSame(42, $summary->median);
        self::assertSame(42, $summary->third_quartile);
    }

    #[Test]
    public function twoValueDistribution(): void
    {
        $summary = new FiveNumberSummary([10, 20]);

        self::assertSame(2, $summary->count);
        self::assertSame(10, $summary->min);
        self::assertSame(20, $summary->max);
        self::assertSame(12.5, $summary->first_quartile);
        self::assertSame(15.0, $summary->median);
        self::assertSame(17.5, $summary->third_quartile);
    }

    #[Test]
    #[DataProvider('percentileProvider')]
    public function percentileCalculatesCorrectValues(
        array $values,
        float $percentile,
        int|float $expected,
    ): void {
        $summary = new FiveNumberSummary(\array_values($values));
        self::assertEquals($expected, $summary->percentile($percentile));
    }

    public static function percentileProvider(): \Iterator
    {
        // Single value - returns int type
        yield 'single value - 0th percentile' => [[42], 0.0, 42];
        yield 'single value - 50th percentile' => [[42], 0.5, 42];
        yield 'single value - 100th percentile' => [[42], 1.0, 42];

        // Two values
        yield 'two values - 0th percentile' => [[10, 20], 0.0, 10];
        yield 'two values - 25th percentile' => [[10, 20], 0.25, 12.5];
        yield 'two values - 50th percentile' => [[10, 20], 0.5, 15.0];
        yield 'two values - 75th percentile' => [[10, 20], 0.75, 17.5];
        yield 'two values - 100th percentile' => [[10, 20], 1.0, 20];

        // Multiple values with known results
        yield 'five values - 0th percentile' => [[1, 2, 3, 4, 5], 0.0, 1];
        yield 'five values - 25th percentile' => [[1, 2, 3, 4, 5], 0.25, 2.0];
        yield 'five values - 50th percentile' => [[1, 2, 3, 4, 5], 0.5, 3];
        yield 'five values - 75th percentile' => [[1, 2, 3, 4, 5], 0.75, 4.0];
        yield 'five values - 100th percentile' => [[1, 2, 3, 4, 5], 1.0, 5];

        // Unsorted input
        yield 'unsorted - 50th percentile' => [[5, 1, 3, 4, 2], 0.5, 3];

        // Float values
        yield 'float values - 50th percentile' => [[1.5, 2.5, 3.5, 4.5], 0.5, 3.0];

        // Edge cases with interpolation
        yield 'even count - 25th percentile' => [[1, 2, 3, 4, 5, 6], 0.25, 2.25];
        yield 'even count - 75th percentile' => [[1, 2, 3, 4, 5, 6], 0.75, 4.75];

        // Negative values
        yield 'negative values - 50th percentile' => [[-5, -3, -1, 0, 2], 0.5, -1];

        // Identical values
        yield 'identical values - 50th percentile' => [[5, 5, 5, 5], 0.5, 5];
    }

    #[Test]
    #[DataProvider('invalidPercentileProvider')]
    public function percentileThrowsExceptionForInvalidValues(float $percentile): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage($percentile . ' is not a valid percentile. Must be between 0 and 1.');

        $summary = new FiveNumberSummary([1, 2, 3]);
        $summary->percentile($percentile);
    }

    public static function invalidPercentileProvider(): \Iterator
    {
        yield 'negative percentile' => [-0.1];
        yield 'percentile greater than 1' => [1.1];
        yield 'percentile much greater than 1' => [2.0];
        yield 'large negative percentile' => [-1.0];
    }

    #[Test]
    public function percentileWithLargeDataset(): void
    {
        $values = \range(1, 100);
        $summary = new FiveNumberSummary($values);

        self::assertSame(1.0, $summary->percentile(0.0));
        self::assertSame(25.75, $summary->percentile(0.25));
        self::assertSame(50.5, $summary->percentile(0.5));
        self::assertSame(75.25, $summary->percentile(0.75));
        self::assertSame(100, $summary->percentile(1.0));
    }

    #[Test]
    public function percentileLinearInterpolationAccuracy(): void
    {
        // Test precise linear interpolation
        $values = [10, 20, 30, 40];
        $summary = new FiveNumberSummary($values);

        // For a 4-element array (indices 0-3), the 33.33% percentile should be:
        // position = (4-1) * 0.3333 = 0.9999
        // index = 0, fractional part ≈ 1.0
        // interpolation between values[0]=10 and values[1]=20
        // result = 10 + 1.0 * (20-10) = 20
        self::assertEqualsWithDelta(19.999, $summary->percentile(0.3333), 0.001);
    }

    #[Test]
    public function handlesFloatAndIntegerMixedValues(): void
    {
        $values = [1, 2.5, 3, 4.8, 5];
        $summary = new FiveNumberSummary($values);

        self::assertSame(5, $summary->count);
        self::assertSame(1, $summary->min);
        self::assertSame(5, $summary->max);
        self::assertSame(3.0, $summary->median);
    }

    #[Test]
    public function countPropertyIsReadonlyAndCorrect(): void
    {
        $summary = new FiveNumberSummary([1, 2, 3, 4, 5]);

        self::assertSame(5, $summary->count);

        // Verify property is readonly via reflection
        $reflection = new \ReflectionClass($summary);
        $property = $reflection->getProperty('count');
        self::assertTrue($property->isReadOnly());
    }

    #[Test]
    public function propertyHooksAccessValuesCorrectly(): void
    {
        $values = [9, 1, 5, 3, 7, 2, 8, 4, 6];
        $summary = new FiveNumberSummary($values);

        // Test that property hooks access the correctly sorted internal values
        self::assertSame(1, $summary->min); // First element after sorting
        self::assertSame(9, $summary->max); // Last element after sorting

        // Verify the quartiles are computed from sorted values, not original input
        self::assertSame(3.0, $summary->first_quartile);
        self::assertSame(5.0, $summary->median);
        self::assertSame(7.0, $summary->third_quartile);
    }

    #[Test]
    public function distributionWithDuplicateValues(): void
    {
        $values = [1, 2, 2, 3, 3, 3, 4, 4, 5];
        $summary = new FiveNumberSummary($values);

        self::assertSame(9, $summary->count);
        self::assertSame(1, $summary->min);
        self::assertSame(5, $summary->max);
        self::assertSame(3.0, $summary->median);
        self::assertSame(2.0, $summary->first_quartile);
        self::assertSame(4.0, $summary->third_quartile);
    }
}
