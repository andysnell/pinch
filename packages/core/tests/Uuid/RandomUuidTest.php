<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Uuid;

use PhoneBurner\Pinch\Uuid\RandomUuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class RandomUuidTest extends TestCase
{
    #[Test]
    public function itIsAUUID(): void
    {
        $uuid = new RandomUuid();

        self::assertTrue(Uuid::isValid((string)$uuid));

        $fields = $uuid->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(Uuid::UUID_TYPE_RANDOM, $fields->getVersion());

        $fields = Uuid::fromString((string)$uuid)->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(Uuid::UUID_TYPE_RANDOM, $fields->getVersion());
    }

    #[Test]
    public function itImplementsUuidInterface(): void
    {
        $uuid = new RandomUuid();

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
        $uuid = new RandomUuid();
        $uuid_string = $uuid->toString();

        $serialized = \serialize($uuid);
        $deserialized = \unserialize($serialized);

        self::assertInstanceOf(RandomUuid::class, $deserialized);
        self::assertSame($uuid_string, $deserialized->toString());
        self::assertTrue($uuid->equals($deserialized));
        self::assertSame(0, $uuid->compareTo($deserialized));
    }

    #[Test]
    public function itCanCompareToOtherUuids(): void
    {
        $uuid1 = new RandomUuid();
        $uuid2 = new RandomUuid();

        // These are random UUIDs so should be different
        self::assertNotSame(0, $uuid1->compareTo($uuid2));
        self::assertSame(0, $uuid1->compareTo($uuid1));
    }

    #[Test]
    public function itCanTestEquality(): void
    {
        $uuid = new RandomUuid();
        $same_uuid = Uuid::fromString($uuid->toString());
        $different_uuid = new RandomUuid();

        self::assertTrue($uuid->equals($same_uuid));
        self::assertTrue($uuid->equals($uuid));
        self::assertFalse($uuid->equals($different_uuid));
        self::assertFalse($uuid->equals(null));
        self::assertFalse($uuid->equals(new \stdClass()));
    }

    #[Test]
    public function eachRandomUuidIsUnique(): void
    {
        $uuids = [];
        for ($i = 0; $i < 100; ++$i) {
            $uuid = new RandomUuid();
            $uuid_string = $uuid->toString();
            self::assertArrayNotHasKey($uuid_string, $uuids, 'Random UUIDs should be unique');
            $uuids[$uuid_string] = true;
        }

        self::assertCount(100, $uuids);
    }
}
