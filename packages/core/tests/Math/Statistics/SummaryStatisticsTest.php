<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Math\Statistics;

use PhoneBurner\Pinch\Math\Statistics\SummaryStatistics;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SummaryStatisticsTest extends TestCase
{
    #[Test]
    public function populationStatisticsCalculation(): void
    {
        $values = [1, 2, 3, 4, 5];
        $stats = SummaryStatistics::population($values);

        self::assertSame(5, $stats->n);
        self::assertContains($stats->mean, [3, 3.0]);
        self::assertSame(1, $stats->min);
        self::assertSame(5, $stats->max);
        self::assertContains($stats->median, [3, 3.0]);
        self::assertSame(4, $stats->range);
        // Population uses n (correct implementation)
        self::assertEqualsWithDelta(1.414, $stats->sd, 0.001);
    }

    #[Test]
    public function sampleStatisticsCalculation(): void
    {
        $values = [1, 2, 3, 4, 5];
        $stats = SummaryStatistics::sample($values);

        self::assertSame(5, $stats->n);
        self::assertContains($stats->mean, [3, 3.0]);
        self::assertSame(1, $stats->min);
        self::assertSame(5, $stats->max);
        self::assertContains($stats->median, [3, 3.0]);
        self::assertSame(4, $stats->range);
        // Sample uses n-1 (correct implementation)
        self::assertEqualsWithDelta(1.581, $stats->sd, 0.001);
    }

    #[Test]
    public function singleValueStatistics(): void
    {
        $values = [42];
        $stats = SummaryStatistics::population($values);

        self::assertSame(1, $stats->n);
        self::assertContains($stats->mean, [42, 42.0]);
        self::assertSame(42, $stats->min);
        self::assertSame(42, $stats->max);
        self::assertContains($stats->median, [42, 42.0]);
        self::assertContains($stats->q1, [42, 42.0]);
        self::assertContains($stats->q3, [42, 42.0]);
        self::assertSame(0, $stats->range);
        self::assertSame(0.0, $stats->sd);
    }

    #[Test]
    public function identicalValuesStatistics(): void
    {
        $values = [5, 5, 5, 5, 5];
        $stats = SummaryStatistics::population($values);

        self::assertSame(5, $stats->n);
        self::assertContains($stats->mean, [5, 5.0]);
        self::assertSame(5, $stats->min);
        self::assertSame(5, $stats->max);
        self::assertContains($stats->median, [5, 5.0]);
        self::assertContains($stats->q1, [5, 5.0]);
        self::assertContains($stats->q3, [5, 5.0]);
        self::assertSame(0, $stats->range);
        self::assertSame(0.0, $stats->sd);
    }

    #[Test]
    public function quartileCalculationsWithEvenCount(): void
    {
        $values = [1, 2, 3, 4, 5, 6, 7, 8];
        $stats = SummaryStatistics::population($values);

        self::assertSame(8, $stats->n);
        self::assertSame(4.5, $stats->mean);
        self::assertSame(1, $stats->min);
        self::assertSame(8, $stats->max);
        self::assertSame(4.5, $stats->median);
        self::assertSame(2.75, $stats->q1);
        self::assertSame(6.25, $stats->q3);
        self::assertSame(7, $stats->range);
    }

    #[Test]
    public function quartileCalculationsWithOddCount(): void
    {
        $values = [1, 2, 3, 4, 5, 6, 7];
        $stats = SummaryStatistics::population($values);

        self::assertSame(7, $stats->n);
        self::assertContains($stats->mean, [4, 4.0]);
        self::assertSame(1, $stats->min);
        self::assertSame(7, $stats->max);
        self::assertContains($stats->median, [4, 4.0]);
        self::assertSame(2.5, $stats->q1);
        self::assertSame(5.5, $stats->q3);
        self::assertSame(6, $stats->range);
    }

    /**
     * @param list<int|float> $values
     */
    #[Test]
    #[DataProvider('floatValuesProvider')]
    public function handlesFloatValues(array $values, float $expected_mean, float $expected_sd): void
    {
        /** @var list<int|float> $values */
        $stats = SummaryStatistics::population($values);

        self::assertEqualsWithDelta($expected_mean, $stats->mean, 0.001);
        self::assertEqualsWithDelta($expected_sd, $stats->sd, 0.001);
    }

    public static function floatValuesProvider(): \Iterator
    {
        yield 'float values' => [
            [1.5, 2.7, 3.1, 4.9, 5.2],
            3.48,
            1.389,
        ];
        yield 'mixed int and float' => [
            [1, 2.5, 3, 4.5, 5],
            3.2,
            1.435,
        ];
        yield 'negative values' => [
            [-2, -1, 0, 1, 2],
            0.0,
            1.414,
        ];
    }

    #[Test]
    public function emptyArrayThrowsUnderflowException(): void
    {
        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Cannot calculate statistics from an empty list.');

        SummaryStatistics::population([]);
    }

    #[Test]
    public function emptyArraySampleThrowsUnderflowException(): void
    {
        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Cannot calculate statistics from an empty list.');

        SummaryStatistics::sample([]);
    }

    #[Test]
    public function rangePropertyCalculation(): void
    {
        $stats = SummaryStatistics::population([10, 20, 30, 40, 50]);

        self::assertSame(40, $stats->range);
        self::assertSame(10, $stats->min);
        self::assertSame(50, $stats->max);
    }

    #[Test]
    public function rangePropertyWithSingleValue(): void
    {
        $stats = SummaryStatistics::population([25]);

        self::assertSame(0, $stats->range);
        self::assertSame(25, $stats->min);
        self::assertSame(25, $stats->max);
    }

    #[Test]
    public function unsortedInputArrayIsHandledCorrectly(): void
    {
        $values = [5, 1, 4, 2, 3];
        $stats = SummaryStatistics::population($values);

        self::assertSame(5, $stats->n);
        self::assertContains($stats->mean, [3, 3.0]);
        self::assertSame(1, $stats->min);
        self::assertSame(5, $stats->max);
        self::assertSame(3.0, $stats->median);
        self::assertSame(2.0, $stats->q1);
        self::assertSame(4.0, $stats->q3);
    }

    #[Test]
    public function populationVsSampleStandardDeviationDifference(): void
    {
        $values = [10, 12, 14, 16, 18];

        $population = SummaryStatistics::population($values);
        $sample = SummaryStatistics::sample($values);

        // Now correctly implemented: sample SD > population SD
        self::assertLessThan($sample->sd, $population->sd);
        self::assertEqualsWithDelta(2.828, $population->sd, 0.001);
        self::assertEqualsWithDelta(3.162, $sample->sd, 0.001);
    }

    #[Test]
    public function largeDatasetStatistics(): void
    {
        // Generate a larger dataset to test performance and accuracy
        $values = \range(1, 1000);
        $stats = SummaryStatistics::population($values);

        self::assertSame(1000, $stats->n);
        self::assertSame(500.5, $stats->mean);
        self::assertSame(1, $stats->min);
        self::assertSame(1000, $stats->max);
        self::assertSame(500.5, $stats->median);
        self::assertSame(250.75, $stats->q1);
        self::assertSame(750.25, $stats->q3);
        self::assertSame(999, $stats->range);
        self::assertEqualsWithDelta(288.675, $stats->sd, 0.001);
    }

    #[Test]
    public function negativeValuesCalculation(): void
    {
        $values = [-10, -5, 0, 5, 10];
        $stats = SummaryStatistics::population($values);

        self::assertSame(5, $stats->n);
        self::assertContains($stats->mean, [0, 0.0]);
        self::assertSame(-10, $stats->min);
        self::assertSame(10, $stats->max);
        self::assertSame(0.0, $stats->median);
        self::assertSame(-5.0, $stats->q1);
        self::assertSame(5.0, $stats->q3);
        self::assertSame(20, $stats->range);
        self::assertEqualsWithDelta(7.071, $stats->sd, 0.001);
    }

    #[Test]
    public function verySmallFloatValues(): void
    {
        $values = [0.001, 0.002, 0.003, 0.004, 0.005];
        $stats = SummaryStatistics::population($values);

        self::assertSame(5, $stats->n);
        self::assertEqualsWithDelta(0.003, $stats->mean, 0.0001);
        self::assertEqualsWithDelta(0.001, $stats->min, 0.0001);
        self::assertEqualsWithDelta(0.005, $stats->max, 0.0001);
        self::assertEqualsWithDelta(0.003, $stats->median, 0.0001);
        self::assertEqualsWithDelta(0.004, $stats->range, 0.0001);
    }

    #[Test]
    public function allReadonlyPropertiesAreAccessible(): void
    {
        $stats = SummaryStatistics::population([1, 2, 3, 4, 5]);

        // Test that all properties are readable
        self::assertIsInt($stats->n);
        self::assertTrue(\is_int($stats->mean) || \is_float($stats->mean));
        self::assertIsFloat($stats->sd);
        self::assertIsInt($stats->min);
        self::assertTrue(\is_int($stats->q1) || \is_float($stats->q1));
        self::assertTrue(\is_int($stats->median) || \is_float($stats->median));
        self::assertTrue(\is_int($stats->q3) || \is_float($stats->q3));
        self::assertIsInt($stats->max);
        self::assertIsInt($stats->range);
    }

    #[Test]
    public function constructorPropertiesAreImmutable(): void
    {
        $stats = SummaryStatistics::population([1, 2, 3]);

        // Verify properties are readonly by checking the reflection
        $reflection = new \ReflectionClass($stats);

        $properties = ['n', 'mean', 'sd', 'min', 'q1', 'median', 'q3', 'max'];
        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            self::assertTrue($property->isReadOnly(), \sprintf('Property %s should be readonly', $propertyName));
        }
    }
}
