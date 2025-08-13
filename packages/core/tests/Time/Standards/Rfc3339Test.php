<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Time\Standards;

use PhoneBurner\Pinch\Time\Standards\Rfc3339;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class Rfc3339Test extends TestCase
{
    #[Test]
    public function constantsHaveExpectedValues(): void
    {
        self::assertSame(\DATE_RFC3339, Rfc3339::DATETIME);
        self::assertSame(\DATE_RFC3339_EXTENDED, Rfc3339::DATETIME_MILLISECOND);
        self::assertSame('Y-m-d\TH:i:s.uP', Rfc3339::DATETIME_MICROSECOND);
        self::assertSame('Y-m-d\TH:i:sp', Rfc3339::DATETIME_Z);
    }

    #[Test]
    public function constantsCanBeUsedWithDateTimeFormatting(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 14:30:45.123456', new \DateTimeZone('UTC'));

        $rfc3339Result = $date->format(Rfc3339::DATETIME);
        self::assertMatchesRegularExpression('/2024-01-15T14:30:45\+00:00/', $rfc3339Result);

        $millisecond_result = $date->format(Rfc3339::DATETIME_MILLISECOND);
        self::assertMatchesRegularExpression('/2024-01-15T14:30:45\.123\+00:00/', $millisecond_result);

        $microsecond_result = $date->format(Rfc3339::DATETIME_MICROSECOND);
        self::assertMatchesRegularExpression('/2024-01-15T14:30:45\.123456\+00:00/', $microsecond_result);

        $z_result = $date->format(Rfc3339::DATETIME_Z);
        self::assertSame('2024-01-15T14:30:45Z', $z_result);
    }

    #[Test]
    public function constantsWorkWithDifferentTimezones(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 14:30:45', new \DateTimeZone('America/New_York'));

        $rfc3339Result = $date->format(Rfc3339::DATETIME);
        self::assertMatchesRegularExpression('/2024-01-15T14:30:45-05:00/', $rfc3339Result);

        $millisecond_result = $date->format(Rfc3339::DATETIME_MILLISECOND);
        self::assertMatchesRegularExpression('/2024-01-15T14:30:45\.000-05:00/', $millisecond_result);
    }
}
