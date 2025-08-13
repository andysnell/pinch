<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Iterator\Sort;

use PhoneBurner\Pinch\Iterator\Sort\Order;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    #[Test]
    public function orderEnumHasExpectedCases(): void
    {
        $expected_cases = ['Ascending', 'Descending'];
        $actual_case_names = \array_map(static fn(Order $case): string => $case->name, Order::cases());

        self::assertSame($expected_cases, $actual_case_names);
    }

    #[Test]
    public function orderEnumCasesAreDistinct(): void
    {
        self::assertNotSame(Order::Ascending, Order::Descending);
    }

    #[Test]
    public function orderEnumCasesCanBeCompared(): void
    {
        self::assertSame(Order::Ascending, Order::Ascending);
        self::assertSame(Order::Descending, Order::Descending);
    }
}
