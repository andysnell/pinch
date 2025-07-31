<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Memory;

use PhoneBurner\Pinch\Memory\Bytes;
use PhoneBurner\Pinch\Memory\MemoryUsage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoryUsage::class)]
final class MemoryUsageTest extends TestCase
{
    #[Test]
    public function currentReturnsMemoryUsageAsBytes(): void
    {
        $memory_usage = MemoryUsage::current();

        self::assertInstanceOf(Bytes::class, $memory_usage);
        self::assertGreaterThan(0, $memory_usage->value);
    }

    #[Test]
    public function currentWithSystemAllocatedReturnsMemoryUsageAsBytes(): void
    {
        $memory_usage = MemoryUsage::current(true);

        self::assertInstanceOf(Bytes::class, $memory_usage);
        self::assertGreaterThan(0, $memory_usage->value);
    }

    #[Test]
    public function currentWithSystemAllocatedUsuallyReturnsHigherValue(): void
    {
        $regular_usage = MemoryUsage::current(false);
        $system_usage = MemoryUsage::current(true);

        // System allocated memory should generally be >= regular usage
        self::assertGreaterThanOrEqual($regular_usage->value, $system_usage->value);
    }

    #[Test]
    public function peakReturnsMemoryUsageAsBytes(): void
    {
        $peak_usage = MemoryUsage::peak();

        self::assertInstanceOf(Bytes::class, $peak_usage);
        self::assertGreaterThan(0, $peak_usage->value);
    }

    #[Test]
    public function peakWithSystemAllocatedReturnsMemoryUsageAsBytes(): void
    {
        $peak_usage = MemoryUsage::peak(true);

        self::assertInstanceOf(Bytes::class, $peak_usage);
        self::assertGreaterThan(0, $peak_usage->value);
    }

    #[Test]
    public function peakWithSystemAllocatedUsuallyReturnsHigherValue(): void
    {
        $regular_peak = MemoryUsage::peak(false);
        $system_peak = MemoryUsage::peak(true);

        // System allocated peak memory should generally be >= regular peak
        self::assertGreaterThanOrEqual($regular_peak->value, $system_peak->value);
    }

    #[Test]
    public function peakIsGreaterThanOrEqualToCurrent(): void
    {
        $current = MemoryUsage::current();
        $peak = MemoryUsage::peak();

        // Peak usage should always be >= current usage
        self::assertGreaterThanOrEqual($current->value, $peak->value);
    }

    #[Test]
    public function resetReturnsMemoryUsageAsBytesAfterResettingPeak(): void
    {
        // Get initial peak
        $initial_peak = MemoryUsage::peak();

        // Create a MemoryUsage instance to call reset
        $memory_usage = new MemoryUsage();
        $reset_result = $memory_usage->reset();

        self::assertInstanceOf(Bytes::class, $reset_result);
        self::assertGreaterThan(0, $reset_result->value);

        // After reset, peak should be close to current usage
        $new_peak = MemoryUsage::peak();
        MemoryUsage::current();

        // The new peak should be close to current (within reasonable bounds)
        self::assertLessThanOrEqual($initial_peak->value, $new_peak->value);
    }

    #[Test]
    public function resetWithSystemAllocatedReturnsMemoryUsageAsBytes(): void
    {
        $memory_usage = new MemoryUsage();
        $reset_result = $memory_usage->reset(true);

        self::assertInstanceOf(Bytes::class, $reset_result);
        self::assertGreaterThan(0, $reset_result->value);
    }

    #[Test]
    public function memoryUsageCanBeAllocatedMemoryIncreases(): void
    {
        $initial_current = MemoryUsage::current();

        // Allocate some memory
        $large_array = \array_fill(0, 10000, 'memory usage test string');

        $after_allocation = MemoryUsage::current();

        // Memory usage should have increased
        self::assertGreaterThan($initial_current->value, $after_allocation->value);

        // Clean up
        unset($large_array);
    }

    #[Test]
    public function memoryUsageValuesAreConsistent(): void
    {
        // Take multiple measurements in quick succession
        $measurement1 = MemoryUsage::current();
        $measurement2 = MemoryUsage::current();
        $measurement3 = MemoryUsage::current();

        // Values should be very close (within small margin due to potential micro-allocations)
        $diff1 = \abs($measurement1->value - $measurement2->value);
        $diff2 = \abs($measurement2->value - $measurement3->value);

        // Allow for small variations (up to 1KB) due to internal PHP operations
        self::assertLessThan(1024, $diff1);
        self::assertLessThan(1024, $diff2);
    }
}
