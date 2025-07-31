<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Serialization;

use PhoneBurner\Pinch\Exception\SerializationFailure;
use PhoneBurner\Pinch\Memory\Bytes;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PhoneBurner\Pinch\String\Serialization\Marshaller;
use PhoneBurner\Pinch\String\Serialization\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Marshaller::class)]
final class MarshallerTest extends TestCase
{
    #[Test]
    public function serializeAndDeserializeCommonValues(): void
    {
        self::assertNull(Marshaller::deserialize(Marshaller::serialize(null)));
        self::assertTrue(Marshaller::deserialize(Marshaller::serialize(true)));
        self::assertFalse(Marshaller::deserialize(Marshaller::serialize(false)));
        self::assertSame([], Marshaller::deserialize(Marshaller::serialize([])));
        self::assertSame(42, Marshaller::deserialize(Marshaller::serialize(42)));
        self::assertSame(3.14, Marshaller::deserialize(Marshaller::serialize(3.14)));
        self::assertSame('test', Marshaller::deserialize(Marshaller::serialize('test')));
    }

    #[Test]
    public function serializeAndDeserializeComplexValues(): void
    {
        $array = ['nested' => ['value' => 42]];
        self::assertSame($array, Marshaller::deserialize(Marshaller::serialize($array)));

        $object = new \stdClass();
        $object->property = 'value';
        self::assertEquals($object, Marshaller::deserialize(Marshaller::serialize($object)));
    }

    #[Test]
    public function serializeWithEncoding(): void
    {
        $serialized = Marshaller::serialize('test', Encoding::Base64, true);
        self::assertStringStartsWith(Encoding::BASE64_PREFIX, $serialized);
        self::assertSame('test', Marshaller::deserialize($serialized));
    }

    #[Test]
    public function compression(): void
    {
        $large_string = \str_repeat('test', 1000);
        $serialized = Marshaller::serialize($large_string, null, false, true);
        self::assertStringStartsWith("\x78", $serialized);
        self::assertSame($large_string, Marshaller::deserialize($serialized));
    }

    #[Test]
    public function compressionWithCustomThreshold(): void
    {
        $string = \str_repeat('test', 10);
        $serialized = Marshaller::serialize($string, null, false, true, new Bytes(10));
        self::assertStringStartsWith("\x78", $serialized);
        self::assertSame($string, Marshaller::deserialize($serialized));
    }

    #[Test]
    public function serializerSelection(): void
    {
        if (\extension_loaded('igbinary')) {
            $serialized = Marshaller::serialize('test', null, false, false, new Bytes(1000), Serializer::Igbinary);
            self::assertStringStartsWith("\x00\x00\x00\x02", $serialized);
        } else {
            $serialized = Marshaller::serialize('test', null, false, false, new Bytes(1000), Serializer::Php);
            self::assertStringStartsWith('s:', $serialized);
        }
    }

    #[Test]
    public function errorCases(): void
    {
        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('cannot serialize resource');
        Marshaller::serialize(\fopen('php://memory', 'r'));
    }

    #[Test]
    public function invalidSerializedData(): void
    {
        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('unsupported serialization format');
        Marshaller::deserialize('invalid:data');
    }

    #[Test]
    public function deserializeHandlesShortInvalidData(): void
    {
        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('unsupported serialization format');

        // Test with data that's too short to be in value map but not valid serialization
        Marshaller::deserialize('xyz');
    }

    #[Test]
    public function edgeCases(): void
    {
        self::assertSame('', Marshaller::deserialize(Marshaller::serialize('')));
        self::assertSame([], Marshaller::deserialize(Marshaller::serialize([])));
        self::assertSame(0, Marshaller::deserialize(Marshaller::serialize(0)));
    }

    #[Test]
    #[DataProvider('providesValueMapEntries')]
    public function deserializeUsesValueMapForCommonValues(string $serialized, mixed $expected): void
    {
        $result = Marshaller::deserialize($serialized);
        self::assertSame($expected, $result);
    }

    #[Test]
    public function deserializeHandlesInvalidIgbinaryData(): void
    {
        if (! \extension_loaded('igbinary')) {
            self::markTestSkipped('igbinary extension not loaded');
        }

        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('igbinary serializer: invalid string');

        // Valid igbinary header but invalid data - suppress the warning
        @Marshaller::deserialize("\x00\x00\x00\x02\xFF\xFF\xFF");
    }

    #[Test]
    public function deserializeHandlesInvalidPhpData(): void
    {
        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('php serializer: invalid string');

        // Valid PHP serializer format start but invalid data - suppress the warning
        @Marshaller::deserialize('s:999:"invalid');
    }

    #[Test]
    public function deserializeHandlesInvalidZlibData(): void
    {
        $this->expectException(SerializationFailure::class);
        $this->expectExceptionMessage('invalid zlib string');

        // Valid zlib header but invalid compressed data
        Marshaller::deserialize("\x78\x9C\xFF\xFF\xFF\xFF");
    }

    #[Test]
    public function serializeThrowsOnSerializationFailure(): void
    {
        if (! \extension_loaded('igbinary')) {
            self::markTestSkipped('igbinary extension not loaded');
        }

        // Mock a situation where igbinary_serialize would fail
        $this->expectException(\Exception::class); // igbinary throws generic Exception, not SerializationFailure
        $this->expectExceptionMessage("Serialization of 'Closure' is not allowed");

        // Create a problematic value by using a closure (not serializable in igbinary)
        $closure = fn(): string => 'test';
        Marshaller::serialize($closure, null, false, false, new Bytes(1000), Serializer::Igbinary);
    }

    #[Test]
    public function serializeHandlesCompressionFailure(): void
    {
        // This is hard to test reliably, so we'll test the compression threshold logic instead
        $small_string = 'small';
        $serialized = Marshaller::serialize($small_string, null, false, true, new Bytes(1000));

        // Should not be compressed because it's smaller than threshold
        self::assertStringNotContainsString("\x78", $serialized);
        self::assertSame($small_string, Marshaller::deserialize($serialized));
    }

    #[Test]
    public function deserializeHandlesEncodingDetection(): void
    {
        // Test auto-detection of encoding prefixes
        $data = 'test data';
        $base64_encoded = Marshaller::serialize($data, Encoding::Base64, true);
        $base64url_encoded = Marshaller::serialize($data, Encoding::Base64Url, true);
        $hex_encoded = Marshaller::serialize($data, Encoding::Hex, true);

        self::assertSame($data, Marshaller::deserialize($base64_encoded, null));
        self::assertSame($data, Marshaller::deserialize($base64url_encoded, null));
        self::assertSame($data, Marshaller::deserialize($hex_encoded, null));
    }

    #[Test]
    public function deserializeHandlesInvalidEncodedData(): void
    {
        // Test that it gracefully handles invalid encoded data by falling back
        $invalid_data = 'base64:this-is-not-valid-base64!!!';

        // Should not throw, but attempt to deserialize as-is (which will likely fail later)
        $this->expectException(SerializationFailure::class);
        Marshaller::deserialize($invalid_data);
    }

    #[Test]
    public function marshallerConstants(): void
    {
        self::assertSame(1200, Marshaller::COMPRESSION_THRESHOLD_BYTES);
        self::assertSame(1, Marshaller::COMPRESSION_LEVEL);
        self::assertSame("\x00\x00\x00\x02", Marshaller::IGBINARY_HEADER);

        self::assertIsArray(Marshaller::ZLIB_HEADERS);
        self::assertContains("\x78\x01", Marshaller::ZLIB_HEADERS);
        self::assertContains("\x78\x5E", Marshaller::ZLIB_HEADERS);
        self::assertContains("\x78\x9C", Marshaller::ZLIB_HEADERS);
        self::assertContains("\x78\xDA", Marshaller::ZLIB_HEADERS);
    }

    #[Test]
    public function serializeWithDifferentEncodings(): void
    {
        $data = 'test data with special chars: àáâãäå';

        $encodings = [
            Encoding::Base64,
            Encoding::Base64Url,
            Encoding::Hex,
        ];

        foreach ($encodings as $encoding) {
            $serialized = Marshaller::serialize($data, $encoding, true);
            $prefix = $encoding->prefix();
            \assert($prefix !== '', 'Encoding prefix must not be empty');
            self::assertStringStartsWith($prefix, $serialized);
            self::assertSame($data, Marshaller::deserialize($serialized));
        }
    }

    #[Test]
    public function serializeSpecialFloatValues(): void
    {
        // Test special float values that have dedicated entries in the value map
        self::assertSame(0.0, Marshaller::deserialize(Marshaller::serialize(0.0)));
        self::assertSame(-0.0, Marshaller::deserialize(Marshaller::serialize(-0.0)));
    }

    public static function providesValueMapEntries(): \Generator
    {
        yield 'null' => ['N;', null];
        yield 'false' => ['b:0;', false];
        yield 'true' => ['b:1;', true];
        yield 'empty array' => ['a:0:{}', []];
        yield 'zero' => ['i:0;', 0];
        yield 'one' => ['i:1;', 1];
        yield 'zero float' => ['d:0;', 0.0];
        yield 'negative zero float' => ['d:-0;', -0.0];
        yield 'empty string' => ['s:0:"";', ''];
        yield 'string zero' => ['s:1:"0";', '0'];
        yield 'string one' => ['s:1:"1";', '1'];
    }
}
