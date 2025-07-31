<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Uuid;

use Generator;
use PhoneBurner\Pinch\Uuid\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Rfc4122\NilUuid;
use Ramsey\Uuid\UuidInterface;
use stdClass;
use Stringable;

final class UuidTest extends TestCase
{
    #[Test]
    public function randomReturnsVersion4UuidInstances(): void
    {
        $uuids = [];
        for ($i = 0; $i < 100; ++$i) {
            $uuid = Uuid::random();
            self::assertMatchesRegularExpression(Uuid::HEX_REGEX, (string)$uuid);
            $fields = $uuid->getFields();
            self::assertInstanceOf(FieldsInterface::class, $fields);
            self::assertSame(2, $fields->getVariant());
            self::assertSame(4, $fields->getVersion());
            $uuids[(string)$uuid] = $uuid;
        }

        self::assertCount(100, $uuids);
    }

    #[Test]
    public function nilReturnsTheNilUuidInstance(): void
    {
        $uuid = Uuid::nil();
        self::assertInstanceOf(NilUuid::class, $uuid);
        self::assertSame('00000000-0000-0000-0000-000000000000', $uuid->toString());
        self::assertSame("\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0", $uuid->getBytes());
        $fields = $uuid->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertTrue($fields->isNil());
        self::assertNull($fields->getVersion());
        self::assertSame($uuid, Uuid::nil());
    }

    #[Test]
    public function orderedReturnsTimestampFirstCombUuidInstances(): void
    {
        $uuid = Uuid::ordered();
        $reduced_comparison = 0;
        for ($i = 0; $i < 100; ++$i) {
            $new_uuid = Uuid::ordered();
            self::assertMatchesRegularExpression(Uuid::HEX_REGEX, (string)$new_uuid);
            self::assertLessThan($new_uuid->toString(), $uuid->toString());
            self::assertLessThan($new_uuid->getBytes(), $uuid->getBytes());
            $fields = $uuid->getFields();
            self::assertInstanceOf(FieldsInterface::class, $fields);
            self::assertSame(2, $fields->getVariant());
            self::assertSame(7, $fields->getVersion());
            $reduced_comparison += $new_uuid->compareTo($uuid);
            $uuid = $new_uuid;
        }

        self::assertSame(100, $reduced_comparison);
    }

    #[Test]
    public function fromStringReturnsMatchingUuid(): void
    {
        $uuid = Uuid::random();
        self::assertTrue($uuid->equals(
            Uuid::instance($uuid->toString()),
        ));
    }

    #[Test]
    public function instanceReturnsSameUuidInterfaceInstance(): void
    {
        $uuid = Uuid::random();
        self::assertSame($uuid, Uuid::instance($uuid));
    }

    #[Test]
    public function parseReturnsSameUuidInterfaceInstance(): void
    {
        $uuid = Uuid::random();
        self::assertSame($uuid, Uuid::parse($uuid));
    }

    #[Test]
    public function instanceCastsStringsToUuidInterface(): void
    {
        $uuid = Uuid::random();

        $uuid_upper_string = \strtoupper($uuid->toString());
        $uuid_lower_string = \strtolower($uuid->toString());
        $uuid_stringable = new readonly class ($uuid) implements Stringable {
            public function __construct(private UuidInterface $uuid)
            {
            }

            public function __toString(): string
            {
                return (string)$this->uuid;
            }
        };

        self::assertTrue($uuid->equals(Uuid::instance($uuid_upper_string)));
        self::assertTrue($uuid->equals(Uuid::instance($uuid_lower_string)));
        self::assertTrue($uuid->equals(Uuid::instance($uuid_stringable)));
    }

    #[Test]
    public function parseCastsStringsToUuidInterface(): void
    {
        $uuid = Uuid::random();

        $uuid_upper_string = \strtoupper($uuid->toString());
        $uuid_lower_string = \strtolower($uuid->toString());
        $uuid_stringable = new readonly class ($uuid) implements Stringable {
            public function __construct(private UuidInterface $uuid)
            {
            }

            public function __toString(): string
            {
                return (string)$this->uuid;
            }
        };

        self::assertTrue($uuid->equals(Uuid::parse($uuid_upper_string)));
        self::assertTrue($uuid->equals(Uuid::parse($uuid_lower_string)));
        self::assertTrue($uuid->equals(Uuid::parse($uuid_stringable)));
    }

    #[Test]
    public function factoryReturnsSameInstance(): void
    {
        $factory1 = Uuid::factory();
        $factory2 = Uuid::factory();

        self::assertSame($factory1, $factory2, 'Factory should return same instance (static)');
    }

    #[Test]
    public function nilReturnsSameInstanceOnMultipleCalls(): void
    {
        $nil1 = Uuid::nil();
        $nil2 = Uuid::nil();

        self::assertSame($nil1, $nil2, 'Nil UUID should return same instance (static)');
        self::assertSame('00000000-0000-0000-0000-000000000000', $nil1->toString());
    }

    #[Test]
    public function parseHandlesNullAndInvalidTypes(): void
    {
        self::assertNull(Uuid::parse(null));
        self::assertNull(Uuid::parse([]));
        self::assertNull(Uuid::parse(42));
        self::assertNull(Uuid::parse(true));
        self::assertNull(Uuid::parse(new \stdClass()));
    }

    #[Test]
    public function parseHandlesExceptionsGracefully(): void
    {
        // Invalid UUID string should return null instead of throwing
        self::assertNull(Uuid::parse('invalid-uuid-format'));
        self::assertNull(Uuid::parse(''));
        self::assertNull(Uuid::parse('too-short'));
    }

    #[Test]
    public function hexRegexConstantMatchesValidUuids(): void
    {
        $uuid = Uuid::random();
        $uuid_string = $uuid->toString();

        self::assertMatchesRegularExpression(Uuid::HEX_REGEX, $uuid_string);

        // Test case variations
        self::assertMatchesRegularExpression(Uuid::HEX_REGEX, \strtolower($uuid_string));

        // Test invalid formats don't match
        self::assertDoesNotMatchRegularExpression(Uuid::HEX_REGEX, 'invalid-format');
        self::assertDoesNotMatchRegularExpression(Uuid::HEX_REGEX, 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        self::assertDoesNotMatchRegularExpression(Uuid::HEX_REGEX, '550e8400-e29b-41d4-a716-44665544000');
    }

    #[DataProvider('provideUncastableUuidValuesOfExpectedType')]
    #[Test]
    public function instanceThrowsExceptionIfCannotCastToUuidInterface(\Stringable|string $value): void
    {
        $this->expectException(InvalidUuidStringException::class);
        Uuid::instance($value);
    }

    #[Test]
    #[DataProvider('provideUncastableUuidValuesOfExpectedType')]
    #[DataProvider('provideUncastableUuidValuesOfWrongType')]
    public function parseReturnsNullIfCannotCastToUuidInterface(mixed $value): void
    {
        self::assertNull(Uuid::parse($value));
    }

    public static function provideUncastableUuidValuesOfWrongType(): Generator
    {
        yield 'null' => [null];
        yield 'object' => [new stdClass()];
        yield 'integer' => [1234567890];
    }

    public static function provideUncastableUuidValuesOfExpectedType(): Generator
    {
        yield 'empty_string' => [''];
        yield 'not-uuid-0' => ['Z0000000-0000-0000-0000-000000000000'];
        yield 'not-uuid-1' => ['00000000-0000-0000-0000-00000000000Z'];
        yield 'too_short' => ['550e8400-e29b-41d4-a716-44665544000'];
        yield 'too_long' => ['550e8400-e29b-41d4-a716-4466554400000'];
        yield 'stringable' => [new class implements Stringable {
            public function __toString(): string
            {
                return 'not-a-uuid';
            }
        }];
    }
}
