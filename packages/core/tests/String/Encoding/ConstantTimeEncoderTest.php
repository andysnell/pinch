<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Encoding;

use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PhoneBurner\Pinch\Tests\String\BinaryString\Fixtures\MockBinaryString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConstantTimeEncoder::class)]
final class ConstantTimeEncoderTest extends TestCase
{
    #[Test]
    public function prefixReturnsExpectedValue(): void
    {
        self::assertSame('hex:', Encoding::Hex->prefix());
        self::assertSame('base64:', Encoding::Base64->prefix());
        self::assertSame('base64:', Encoding::Base64NoPadding->prefix());
        self::assertSame('base64url:', Encoding::Base64Url->prefix());
        self::assertSame('base64url:', Encoding::Base64UrlNoPadding->prefix());
    }

    #[Test]
    public function regexReturnsExpectedValue(): void
    {
        self::assertSame('/^[A-Fa-f0-9]+$/', Encoding::Hex->regex());
        self::assertSame('/^[A-Za-z0-9+\/]+={0,2}$/', Encoding::Base64->regex());
        self::assertSame('/^[A-Za-z0-9+\/]+$/', Encoding::Base64NoPadding->regex());
        self::assertSame('/^[A-Za-z0-9-_]+={0,2}$/', Encoding::Base64Url->regex());
        self::assertSame('/^[A-Za-z0-9-_]+$/', Encoding::Base64UrlNoPadding->regex());
    }

    #[Test]
    #[DataProvider('providesEncodingHappyPathTests')]
    public function happyPathEncodingAndDecoding(
        Encoding $encoding,
        bool $prefix,
        string $input,
        string $expected,
    ): void {
        self::assertSame($expected, ConstantTimeEncoder::encode($encoding, $input, $prefix));
        self::assertSame($input, ConstantTimeEncoder::decode($encoding, $expected));
    }

    public static function providesEncodingHappyPathTests(): \Generator
    {
        yield [Encoding::Hex, false, 'hello', '68656c6c6f'];
        yield [Encoding::Hex, true, 'hello', 'hex:68656c6c6f'];

        yield [Encoding::Base64, false, 'hello', 'aGVsbG8='];
        yield [Encoding::Base64, true, 'hello', 'base64:aGVsbG8='];

        yield [Encoding::Base64NoPadding, false, 'hello', 'aGVsbG8'];
        yield [Encoding::Base64NoPadding, true, 'hello', 'base64:aGVsbG8'];

        yield [Encoding::Base64Url, false, 'hello', 'aGVsbG8='];
        yield [Encoding::Base64Url, true, 'hello', 'base64url:aGVsbG8='];

        yield [Encoding::Base64UrlNoPadding, false, 'hello', 'aGVsbG8'];
        yield [Encoding::Base64UrlNoPadding, true, 'hello', 'base64url:aGVsbG8'];

        yield [Encoding::Hex, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', '54686520517569636b2042726f776e20466f78204a756d7073204f76657220546865204c617a7920446f67'];
        yield [Encoding::Hex, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'hex:54686520517569636b2042726f776e20466f78204a756d7073204f76657220546865204c617a7920446f67'];

        yield [Encoding::Base64, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];
        yield [Encoding::Base64, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];

        yield [Encoding::Base64NoPadding, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];
        yield [Encoding::Base64NoPadding, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];

        yield [Encoding::Base64Url, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];
        yield [Encoding::Base64Url, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64url:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];

        yield [Encoding::Base64UrlNoPadding, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];
        yield [Encoding::Base64UrlNoPadding, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64url:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];

        yield [Encoding::Hex , false, '📞🔥', 'f09f939ef09f94a5'];
        yield [Encoding::Base64, false, '📞🔥', '8J+TnvCflKU='];
        yield [Encoding::Base64NoPadding, false, '📞🔥', '8J+TnvCflKU'];
        yield [Encoding::Base64Url, false, '📞🔥', '8J-TnvCflKU='];
        yield [Encoding::Base64UrlNoPadding, false, '📞🔥', '8J-TnvCflKU'];

        yield [Encoding::Hex , false, "\xff\xff\xfe\xff", 'fffffeff'];
        yield [Encoding::Base64, false, "\xff\xff\xfe\xff", '///+/w=='];
        yield [Encoding::Base64NoPadding, false, "\xff\xff\xfe\xff", '///+/w'];
        yield [Encoding::Base64Url, false, "\xff\xff\xfe\xff", '___-_w=='];
        yield [Encoding::Base64UrlNoPadding, false, "\xff\xff\xfe\xff", '___-_w'];
    }

    #[Test]
    #[DataProvider('providesInvalidInputForDecoding')]
    public function decodeThrowsExceptionOnInvalidInput(
        Encoding $encoding,
        string $input,
    ): void {
        $this->expectException(\UnexpectedValueException::class);
        ConstantTimeEncoder::decode($encoding, $input);
    }

    public static function providesInvalidInputForDecoding(): \Generator
    {
        yield [Encoding::Hex, 'invalid'];
        yield [Encoding::Hex, '68656c6c6'];
        yield [Encoding::Base64, 'this is an invalid base64 string!'];
        yield [Encoding::Base64NoPadding, 'this is an invalid base64 string!'];
        yield [Encoding::Base64Url, 'this is an invalid base64 string!'];
        yield [Encoding::Base64UrlNoPadding, 'this is an invalid base64 string!'];
    }

    #[Test]
    public function hexPrefixesAreStripped(): void
    {
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Hex, 'hex:68656c6c6f'));
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Hex, '0x68656c6c6f'));
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Hex, 'hex:0x68656c6c6f'));
    }

    #[Test]
    public function equalsReturnsTrueForIdenticalStrings(): void
    {
        self::assertTrue(ConstantTimeEncoder::equals('test', 'test'));
        self::assertTrue(ConstantTimeEncoder::equals('hello world', 'hello world'));
        self::assertTrue(ConstantTimeEncoder::equals('', ''));
    }

    #[Test]
    public function equalsReturnsFalseForDifferentStrings(): void
    {
        self::assertFalse(ConstantTimeEncoder::equals('test', 'test2'));
        self::assertFalse(ConstantTimeEncoder::equals('hello', 'world'));
        self::assertFalse(ConstantTimeEncoder::equals('test', ''));
        self::assertFalse(ConstantTimeEncoder::equals('', 'test'));
    }

    #[Test]
    public function equalsReturnsFalseForNullInput(): void
    {
        self::assertFalse(ConstantTimeEncoder::equals('test', null));
        self::assertFalse(ConstantTimeEncoder::equals('', null));
    }

    #[Test]
    public function equalsWorksWithBinaryStrings(): void
    {
        $binaryString = new MockBinaryString('test data');

        self::assertTrue(ConstantTimeEncoder::equals($binaryString, 'test data'));
        self::assertTrue(ConstantTimeEncoder::equals('test data', $binaryString));
        self::assertFalse(ConstantTimeEncoder::equals($binaryString, 'different data'));
    }

    #[Test]
    public function equalsWorksWithStringableObjects(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'stringable content';
            }
        };

        self::assertTrue(ConstantTimeEncoder::equals($stringable, 'stringable content'));
        self::assertTrue(ConstantTimeEncoder::equals('stringable content', $stringable));
        self::assertFalse(ConstantTimeEncoder::equals($stringable, 'different content'));
    }

    #[Test]
    public function stringStartsWithReturnsTrueWhenHaystackStartsWithNeedle(): void
    {
        self::assertTrue(ConstantTimeEncoder::stringStartsWith('hello world', 'hello'));
        self::assertTrue(ConstantTimeEncoder::stringStartsWith('hello world', 'hello world'));
        self::assertTrue(ConstantTimeEncoder::stringStartsWith('test', 'test'));
        self::assertTrue(ConstantTimeEncoder::stringStartsWith('anything', ''));
    }

    #[Test]
    public function stringStartsWithReturnsFalseWhenHaystackDoesNotStartWithNeedle(): void
    {
        self::assertFalse(ConstantTimeEncoder::stringStartsWith('hello world', 'world'));
        self::assertFalse(ConstantTimeEncoder::stringStartsWith('hello world', 'Hello'));
        self::assertFalse(ConstantTimeEncoder::stringStartsWith('test', 'testing'));
        self::assertFalse(ConstantTimeEncoder::stringStartsWith('', 'anything'));
    }

    #[Test]
    public function stringStartsWithWorksWithBinaryStrings(): void
    {
        $haystack = new MockBinaryString('hello world');
        $needle = new MockBinaryString('hello');

        self::assertTrue(ConstantTimeEncoder::stringStartsWith($haystack, $needle));
        self::assertTrue(ConstantTimeEncoder::stringStartsWith($haystack, 'hello'));
        self::assertTrue(ConstantTimeEncoder::stringStartsWith('hello world', $needle));
    }

    #[Test]
    public function decodeWithStrictModeThrowsOnInvalidInput(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid Encoded String');

        ConstantTimeEncoder::decode(Encoding::Hex, 'invalid-hex', true);
    }

    #[Test]
    public function decodeWithStrictModeSucceedsOnValidInput(): void
    {
        $result = ConstantTimeEncoder::decode(Encoding::Hex, '68656c6c6f', true);

        self::assertSame('hello', $result);
    }

    #[Test]
    public function decodeHandlesBase64Prefixes(): void
    {
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Base64, 'base64:aGVsbG8='));
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Base64Url, 'base64url:aGVsbG8='));
    }

    #[Test]
    public function decodeHandlesBase64WithoutPrefixes(): void
    {
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Base64, 'aGVsbG8='));
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Base64Url, 'aGVsbG8='));
    }

    #[Test]
    public function decodeHandlesBase64WithExtraPadding(): void
    {
        // Test that it gracefully handles extra padding or missing padding
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Base64, 'aGVsbG8'));
        self::assertSame('hello', ConstantTimeEncoder::decode(Encoding::Base64Url, 'aGVsbG8'));
    }
}
