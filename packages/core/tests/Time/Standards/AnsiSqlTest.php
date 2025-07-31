<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Standards;

use PhoneBurner\Pinch\Time\Standards\AnsiSql;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AnsiSqlTest extends TestCase
{
    #[Test]
    public function constantsHaveExpectedValues(): void
    {
        self::assertSame('Y-m-d H:i:s', AnsiSql::DATETIME);
        self::assertSame('Y-m-d', AnsiSql::DATE);
        self::assertSame('H:i:s', AnsiSql::TIME);
        self::assertSame('Y', AnsiSql::YEAR);
    }

    #[Test]
    public function nullConstantsHaveExpectedValues(): void
    {
        self::assertSame('0000-00-00 00:00:00', AnsiSql::NULL_DATETIME);
        self::assertSame('0000-00-00', AnsiSql::NULL_DATE);
        self::assertSame('00:00:00', AnsiSql::NULL_TIME);
    }

    #[Test]
    public function constantsCanBeUsedWithDateTimeFormatting(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 14:30:45');

        self::assertSame('2024-01-15 14:30:45', $date->format(AnsiSql::DATETIME));
        self::assertSame('2024-01-15', $date->format(AnsiSql::DATE));
        self::assertSame('14:30:45', $date->format(AnsiSql::TIME));
        self::assertSame('2024', $date->format(AnsiSql::YEAR));
    }
}
