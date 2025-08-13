<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\BinaryString\Traits;

use PhoneBurner\Pinch\Exception\SerializationProhibited;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures\MockBinaryString;
use PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures\MockGeneratableBinaryString;
use PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures\MockImportableBinaryString;
use PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures\MockSerializationProhibitedBinaryString;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BinaryStringTraitsTest extends TestCase
{
    private string $assignment_dummy = '';

    #[Test]
    public function binaryStringExportBehaviorExportsWithDefaultEncoding(): void
    {
        $binary_string = new MockBinaryString('test data');

        $exported = $binary_string->export();

        // Base64Url encoding of 'test data'
        self::assertSame('dGVzdCBkYXRh', $exported);
    }

    #[Test]
    public function binaryStringExportBehaviorExportsWithSpecificEncoding(): void
    {
        $binary_string = new MockBinaryString('test data');

        $exported = $binary_string->export(Encoding::Hex);

        self::assertSame('746573742064617461', $exported);
    }

    #[Test]
    public function binaryStringExportBehaviorExportsWithPrefix(): void
    {
        $binary_string = new MockBinaryString('test data');

        $exported = $binary_string->export(Encoding::Hex, true);

        self::assertSame('hex:746573742064617461', $exported);
    }

    #[Test]
    public function binaryStringFromRandomBytesGeneratesCorrectLength(): void
    {
        $generated = MockGeneratableBinaryString::generate();

        self::assertSame(16, $generated->length());
        self::assertSame(16, \strlen($generated->bytes()));
    }

    #[Test]
    public function binaryStringFromRandomBytesGeneratesUniqueData(): void
    {
        $first = MockGeneratableBinaryString::generate();
        $second = MockGeneratableBinaryString::generate();

        self::assertNotSame($first->bytes(), $second->bytes());
    }

    #[Test]
    public function binaryStringImportBehaviorImportsBase64UrlString(): void
    {
        $imported = MockImportableBinaryString::import('dGVzdCBkYXRh');

        self::assertSame('test data', $imported->bytes());
    }

    #[Test]
    public function binaryStringImportBehaviorImportsHexString(): void
    {
        $imported = MockImportableBinaryString::import('746573742064617461', Encoding::Hex);

        self::assertSame('test data', $imported->bytes());
    }

    #[Test]
    public function binaryStringImportBehaviorImportsWithPrefix(): void
    {
        $imported = MockImportableBinaryString::import('hex:746573742064617461', Encoding::Hex);

        self::assertSame('test data', $imported->bytes());
    }

    #[Test]
    public function binaryStringImportBehaviorThrowsOnInvalidData(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        MockImportableBinaryString::import('invalid-hex', Encoding::Hex);
    }

    #[Test]
    public function binaryStringTryImportReturnsNullOnInvalidData(): void
    {
        $result = MockImportableBinaryString::tryImport('invalid-hex', Encoding::Hex);

        self::assertNull($result);
    }

    #[Test]
    public function binaryStringTryImportReturnsNullOnNullInput(): void
    {
        $result = MockImportableBinaryString::tryImport(null);

        self::assertNull($result);
    }

    #[Test]
    public function binaryStringTryImportImportsValidData(): void
    {
        $result = MockImportableBinaryString::tryImport('dGVzdCBkYXRh');

        self::assertNotNull($result);
        self::assertSame('test data', $result->bytes());
    }

    #[Test]
    public function binaryStringProhibitsSerializationThrowsOnToString(): void
    {
        self::assertSame('', $this->assignment_dummy);
        $this->expectException(SerializationProhibited::class);
        $this->assignment_dummy = (string)new MockSerializationProhibitedBinaryString('test');
    }

    #[Test]
    public function binaryStringProhibitsSerializationThrowsOnSerialize(): void
    {
        $this->expectException(SerializationProhibited::class);

        $binary_string = new MockSerializationProhibitedBinaryString('test');
        $binary_string->__serialize();
    }

    #[Test]
    public function binaryStringProhibitsSerializationThrowsOnUnserialize(): void
    {
        $this->expectException(SerializationProhibited::class);

        $binary_string = new MockSerializationProhibitedBinaryString('test');
        $binary_string->__unserialize([]);
    }

    #[Test]
    public function binaryStringProhibitsSerializationThrowsOnJsonSerialize(): void
    {
        $this->expectException(SerializationProhibited::class);

        $binary_string = new MockSerializationProhibitedBinaryString('test');
        $binary_string->jsonSerialize();
    }
}
