<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Utility;

use PhoneBurner\Pinch\Utility\ResultCounter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultCounterTest extends TestCase
{
    #[Test]
    public function happyPath(): void
    {
        $counter = new ResultCounter();
        self::assertSame(0, $counter->success);
        self::assertSame(0, $counter->warning);
        self::assertSame(0, $counter->error);
        self::assertCount(0, $counter);

        ++$counter->success;
        ++$counter->warning;
        ++$counter->error;

        self::assertSame(1, $counter->success);
        self::assertSame(1, $counter->warning);
        self::assertSame(1, $counter->error);
        self::assertCount(3, $counter);
    }
}
