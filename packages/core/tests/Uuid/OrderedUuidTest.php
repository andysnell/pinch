<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Uuid;

use PhoneBurner\Pinch\Uuid\OrderedUuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class OrderedUuidTest extends TestCase
{
    #[Test]
    public function itIsAUUID(): void
    {
        $uuid = new OrderedUuid();

        self::assertTrue(Uuid::isValid((string)$uuid));
        $fields = $uuid->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(Uuid::UUID_TYPE_UNIX_TIME, $fields->getVersion());

        $fields = Uuid::fromString((string)$uuid)->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(Uuid::UUID_TYPE_UNIX_TIME, $fields->getVersion());
    }

    #[Test]
    public function itImplementsUuidInterface(): void
    {
        $uuid = new OrderedUuid();

        self::assertInstanceOf(UuidInterface::class, $uuid);
        self::assertIsString($uuid->toString());
        self::assertIsString((string)$uuid);
        self::assertIsString($uuid->jsonSerialize());
        self::assertIsString($uuid->getBytes());
        self::assertIsString($uuid->getUrn());
        self::assertStringStartsWith('urn:uuid:', $uuid->getUrn());
    }

    #[Test]
    public function itCanBeSerialized(): void
    {
        $uuid = new OrderedUuid();
        $uuid_string = $uuid->toString();

        $serialized = \serialize($uuid);
        $deserialized = \unserialize($serialized);

        self::assertInstanceOf(OrderedUuid::class, $deserialized);
        self::assertSame($uuid_string, $deserialized->toString());
        self::assertTrue($uuid->equals($deserialized));
        self::assertSame(0, $uuid->compareTo($deserialized));
    }

    #[Test]
    public function itCanCompareToOtherUuids(): void
    {
        $uuid1 = new OrderedUuid();
        $uuid2 = new OrderedUuid();

        // Since OrderedUuids are time-based, uuid2 should be greater than uuid1
        self::assertLessThan(0, $uuid1->compareTo($uuid2));
        self::assertGreaterThan(0, $uuid2->compareTo($uuid1));
        self::assertSame(0, $uuid1->compareTo($uuid1));
    }

    #[Test]
    public function itCanTestEquality(): void
    {
        $uuid = new OrderedUuid();
        $same_uuid = Uuid::fromString($uuid->toString());
        $different_uuid = new OrderedUuid();

        self::assertTrue($uuid->equals($same_uuid));
        self::assertTrue($uuid->equals($uuid));
        self::assertFalse($uuid->equals($different_uuid));
        self::assertFalse($uuid->equals(null));
        self::assertFalse($uuid->equals(new \stdClass()));
    }
}
