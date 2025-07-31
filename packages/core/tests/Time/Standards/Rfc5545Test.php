<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Standards;

use PhoneBurner\Pinch\Time\Standards\Rfc5545;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class Rfc5545Test extends TestCase
{
    #[Test]
    public function constantsHaveExpectedValues(): void
    {
        self::assertSame('Ymd\THis\Z', Rfc5545::DATETIME);
        self::assertSame('Ymd', Rfc5545::DATE);
    }

    #[Test]
    public function constantsCanBeUsedWithDateTimeFormatting(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 14:30:45', new \DateTimeZone('UTC'));

        self::assertSame('20240115T143045Z', $date->format(Rfc5545::DATETIME));
        self::assertSame('20240115', $date->format(Rfc5545::DATE));
    }

    #[Test]
    public function datetimeFormatProducesValidICalendarFormat(): void
    {
        $date = new \DateTimeImmutable('2024-12-31 23:59:59', new \DateTimeZone('UTC'));
        $formatted = $date->format(Rfc5545::DATETIME);

        // Valid iCalendar datetime format: YYYYMMDDTHHMMSSZ
        self::assertMatchesRegularExpression('/^\d{8}T\d{6}Z$/', $formatted);
        self::assertSame('20241231T235959Z', $formatted);
    }

    #[Test]
    public function dateFormatProducesValidICalendarFormat(): void
    {
        $date = new \DateTimeImmutable('2024-02-29', new \DateTimeZone('UTC'));
        $formatted = $date->format(Rfc5545::DATE);

        // Valid iCalendar date format: YYYYMMDD
        self::assertMatchesRegularExpression('/^\d{8}$/', $formatted);
        self::assertSame('20240229', $formatted);
    }
}
