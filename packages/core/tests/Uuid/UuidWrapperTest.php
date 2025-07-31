<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Uuid;

use PhoneBurner\Pinch\Uuid\UuidString;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UuidWrapperTest extends TestCase
{
    #[Test]
    public function serializeThrowsLogicException(): void
    {
        $uuid_string = new UuidString('550e8400-e29b-41d4-a716-446655440000');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Serializes with __serialize Magic Method');

        /** @phpstan-ignore method.deprecated */
        $uuid_string->serialize();
    }

    #[Test]
    public function unserializeThrowsLogicException(): void
    {
        $uuid_string = new UuidString('550e8400-e29b-41d4-a716-446655440000');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Deserializes with __unserialize Magic Method');

        /** @phpstan-ignore method.deprecated */
        $uuid_string->unserialize('some-data');
    }

    #[Test]
    public function deprecatedMethodsProperlyDelegate(): void
    {
        // Use a UUID v4 that we can verify expected values for
        $uuid_string = new UuidString('550e8400-e29b-41d4-a716-446655440000');
        $ramsey_uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        /** @phpstan-ignore method.deprecated */
        self::assertIsObject($uuid_string->getNumberConverter());

        // Compare with actual Ramsey UUID values instead of hardcoded ones
        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getClockSeqHiAndReservedHex(), $uuid_string->getClockSeqHiAndReservedHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getClockSeqLowHex(), $uuid_string->getClockSeqLowHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getClockSequenceHex(), $uuid_string->getClockSequenceHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getNodeHex(), $uuid_string->getNodeHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getLeastSignificantBitsHex(), $uuid_string->getLeastSignificantBitsHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getMostSignificantBitsHex(), $uuid_string->getMostSignificantBitsHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getTimeLowHex(), $uuid_string->getTimeLowHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getTimeMidHex(), $uuid_string->getTimeMidHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getTimeHiAndVersionHex(), $uuid_string->getTimeHiAndVersionHex());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getVariant(), $uuid_string->getVariant());

        /** @phpstan-ignore method.deprecated, method.deprecated */
        self::assertSame($ramsey_uuid->getVersion(), $uuid_string->getVersion());
    }
}
