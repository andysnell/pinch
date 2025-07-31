<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String;

use PhoneBurner\Pinch\String\RegExp;
use PhoneBurner\Pinch\Tests\String\Fixtures\TestBinaryString;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

use function PhoneBurner\Pinch\String\bytes;
use function PhoneBurner\Pinch\String\class_shortname;
use function PhoneBurner\Pinch\String\str_camel;
use function PhoneBurner\Pinch\String\str_cast;
use function PhoneBurner\Pinch\String\str_dot;
use function PhoneBurner\Pinch\String\str_enquote;
use function PhoneBurner\Pinch\String\str_kabob;
use function PhoneBurner\Pinch\String\str_lpad;
use function PhoneBurner\Pinch\String\str_ltrim;
use function PhoneBurner\Pinch\String\str_pascal;
use function PhoneBurner\Pinch\String\str_prefix;
use function PhoneBurner\Pinch\String\str_rpad;
use function PhoneBurner\Pinch\String\str_rtrim;
use function PhoneBurner\Pinch\String\str_screaming;
use function PhoneBurner\Pinch\String\str_snake;
use function PhoneBurner\Pinch\String\str_strip;
use function PhoneBurner\Pinch\String\str_suffix;
use function PhoneBurner\Pinch\String\str_to_stream;
use function PhoneBurner\Pinch\String\str_to_stringable;
use function PhoneBurner\Pinch\String\str_trim;
use function PhoneBurner\Pinch\String\str_truncate;
use function PhoneBurner\Pinch\String\str_ucwords;

#[CoversFunction('PhoneBurner\Pinch\String\bytes')]
#[CoversFunction('PhoneBurner\Pinch\String\class_shortname')]
#[CoversFunction('PhoneBurner\Pinch\String\str_camel')]
#[CoversFunction('PhoneBurner\Pinch\String\str_cast')]
#[CoversFunction('PhoneBurner\Pinch\String\str_dot')]
#[CoversFunction('PhoneBurner\Pinch\String\str_enquote')]
#[CoversFunction('PhoneBurner\Pinch\String\str_kabob')]
#[CoversFunction('PhoneBurner\Pinch\String\str_lpad')]
#[CoversFunction('PhoneBurner\Pinch\String\str_ltrim')]
#[CoversFunction('PhoneBurner\Pinch\String\str_pascal')]
#[CoversFunction('PhoneBurner\Pinch\String\str_prefix')]
#[CoversFunction('PhoneBurner\Pinch\String\str_rpad')]
#[CoversFunction('PhoneBurner\Pinch\String\str_rtrim')]
#[CoversFunction('PhoneBurner\Pinch\String\str_screaming')]
#[CoversFunction('PhoneBurner\Pinch\String\str_snake')]
#[CoversFunction('PhoneBurner\Pinch\String\str_to_stream')]
#[CoversFunction('PhoneBurner\Pinch\String\str_strip')]
#[CoversFunction('PhoneBurner\Pinch\String\str_suffix')]
#[CoversFunction('PhoneBurner\Pinch\String\str_to_stringable')]
#[CoversFunction('PhoneBurner\Pinch\String\str_trim')]
#[CoversFunction('PhoneBurner\Pinch\String\str_truncate')]
#[CoversFunction('PhoneBurner\Pinch\String\str_ucwords')]
final class StringFunctionsTest extends TestCase
{
    #[Test]
    public function strCastConvertsValueToString(): void
    {
        self::assertSame('42', str_cast(42));
        self::assertSame('42.5', str_cast(42.5));
        self::assertSame('', str_cast(null));
        self::assertSame('1', str_cast(true));
        self::assertSame('', str_cast(false));
    }

    #[Test]
    public function strCastReturnsStringAsIs(): void
    {
        self::assertSame('hello', str_cast('hello'));
    }

    #[Test]
    public function strCastThrowsForInvalidTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        str_cast(['array']);
    }

    #[Test]
    public function strObjectConvertsStringToStringable(): void
    {
        $stringable = str_to_stringable('test');

        self::assertInstanceOf(\Stringable::class, $stringable);
        self::assertSame('test', (string)$stringable);
    }

    #[Test]
    public function strObjectReturnsStringableAsIs(): void
    {
        $original = new class implements \Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };

        $result = str_to_stringable($original);
        self::assertSame($original, $result);
    }

    #[Test]
    public function strTrimRemovesWhitespace(): void
    {
        self::assertSame('hello', str_trim('  hello  '));
        self::assertSame('hello', str_trim("\n\thello\r\n"));
    }

    #[Test]
    public function strTrimRemovesAdditionalCharacters(): void
    {
        self::assertSame('hello', str_trim('--hello--', ['-']));
    }

    #[Test]
    public function strStartAddsPrefix(): void
    {
        self::assertSame('/path', str_prefix('path', '/'));
        self::assertSame('/path', str_prefix('/path', '/'));
    }

    #[Test]
    public function strEndAddsSuffix(): void
    {
        self::assertSame('path/', str_suffix('path', '/'));
        self::assertSame('path/', str_suffix('path/', '/'));
    }

    #[Test]
    public function strShortnameReturnsClassName(): void
    {
        self::assertSame('ClassName', class_shortname('Namespace\\ClassName'));
        self::assertSame('ClassName', class_shortname('ClassName'));
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strSnakeConvertsToSnakeCase(string $input, string $expected): void
    {
        self::assertSame($expected, str_snake($input));
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strCamelConvertsToCamelCase(string $input, string $expectedSnake): void
    {
        // Convert expected snake case to camel case for comparison
        $words = \explode('_', $expectedSnake);
        $camelCase = $words[0] . \implode('', \array_map(\ucfirst(...), \array_slice($words, 1)));

        self::assertSame($camelCase, str_camel($input));
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strPascalConvertsToPascalCase(string $input, string $expectedSnake): void
    {
        // Convert expected snake case to pascal case for comparison
        $words = \explode('_', $expectedSnake);
        $pascalCase = \implode('', \array_map(\ucfirst(...), $words));

        self::assertSame($pascalCase, str_pascal($input));
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strKabobConvertsToKabobCase(string $input, string $expectedSnake): void
    {
        $expected = \str_replace('_', '-', $expectedSnake);
        self::assertSame($expected, str_kabob($input));
    }

    #[Test]
    public function strEnquoteWrapsStringInQuotes(): void
    {
        self::assertSame('"hello"', str_enquote('hello'));
        self::assertSame("'hello'", str_enquote('hello', "'"));
    }

    #[Test]
    public function strRpadPadsRight(): void
    {
        self::assertSame('hello     ', str_rpad('hello', 10));
        self::assertSame('hello00000', str_rpad('hello', 10, '0'));
    }

    #[Test]
    public function strLpadPadsLeft(): void
    {
        self::assertSame('     hello', str_lpad('hello', 10));
        self::assertSame('00000hello', str_lpad('hello', 10, '0'));
    }

    #[Test]
    public function strTruncateLimitsLength(): void
    {
        self::assertSame('hello...', str_truncate('hello world', 8));
        self::assertSame('hello world', str_truncate('hello world', 20));
    }

    #[Test]
    public function strToStreamCreatesMemoryStream(): void
    {
        $stream = str_to_stream('test content');

        self::assertIsResource($stream);
        self::assertSame('test content', \stream_get_contents($stream));

        \fclose($stream);
    }

    #[Test]
    public function strToStreamHandlesEmptyString(): void
    {
        $stream = str_to_stream();

        self::assertIsResource($stream);
        self::assertSame('', \stream_get_contents($stream));

        \fclose($stream);
    }

    #[Test]
    public function strToStreamHandlesNumericTypes(): void
    {
        $stream = str_to_stream(42);

        self::assertIsResource($stream);
        self::assertSame('42', \stream_get_contents($stream));

        \fclose($stream);
    }

    #[Test]
    public function strLtrimRemovesLeftWhitespace(): void
    {
        self::assertSame('hello  ', str_ltrim('  hello  '));
        self::assertSame("hello\r\n", str_ltrim("\n\thello\r\n"));
    }

    #[Test]
    public function strLtrimRemovesAdditionalCharacters(): void
    {
        self::assertSame('hello--', str_ltrim('--hello--', ['-']));
    }

    #[Test]
    public function strRtrimRemovesRightWhitespace(): void
    {
        self::assertSame('  hello', str_rtrim('  hello  '));
        self::assertSame("\n\thello", str_rtrim("\n\thello\r\n"));
    }

    #[Test]
    public function strRtrimRemovesAdditionalCharacters(): void
    {
        self::assertSame('--hello', str_rtrim('--hello--', ['-']));
    }

    #[Test]
    public function strStripRemovesStringSearch(): void
    {
        self::assertSame('helloworld', str_strip('hello-world', '-'));
        self::assertSame('hello', str_strip('hello--', '--'));
    }

    #[Test]
    public function strStripUsesRegExpPattern(): void
    {
        $regexp = new RegExp('[0-9]+');
        self::assertSame('helloworld', str_strip('hello123world456', $regexp));
    }

    #[Test]
    public function strStripThrowsOnInvalidRegExp(): void
    {
        $this->expectException(\RuntimeException::class);
        $regexp = new RegExp('[');
        str_strip('test', $regexp);
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strDotConvertsToDotCase(string $input, string $expectedSnake): void
    {
        $expected = \str_replace('_', '.', $expectedSnake);
        self::assertSame($expected, str_dot($input));
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strScreamingConvertsToScreamingCase(string $input, string $expectedSnake): void
    {
        $expected = \strtoupper($expectedSnake);
        self::assertSame($expected, str_screaming($input));
    }

    #[Test]
    #[DataProvider('providesStringCases')]
    public function strUcwordsConvertsToTitleCase(string $input, string $expectedSnake): void
    {
        $words = \explode('_', $expectedSnake);
        $expected = \implode(' ', \array_map(\ucfirst(...), $words));
        self::assertSame($expected, str_ucwords($input));
    }

    #[Test]
    public function strTruncateThrowsForNegativeMaxLength(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Max Length Must Be Non-Negative');
        str_truncate('test', -1);
    }

    #[Test]
    public function strTruncateThrowsWhenTrimMarkerTooLong(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Trim Marker Length Must Be Less Than or Equal to Max Length');
        str_truncate('test', 2, '...');
    }

    #[Test]
    public function strTruncateHandlesStringableInput(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'hello world';
            }
        };

        self::assertSame('hello...', str_truncate($stringable, 8));
    }

    #[Test]
    public function classShortnameHandlesObject(): void
    {
        $object = new \stdClass();
        self::assertSame('stdClass', class_shortname($object));
    }

    #[Test]
    public function bytesHandlesString(): void
    {
        self::assertSame('test', bytes('test'));
    }

    #[Test]
    public function bytesHandlesBinaryString(): void
    {
        $binaryString = new TestBinaryString('binary-data');

        self::assertSame('binary-data', bytes($binaryString));
    }

    #[Test]
    public function bytesHandlesUuid(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame($uuid->getBytes(), bytes($uuid));
    }

    #[Test]
    public function bytesHandlesStringable(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'stringable-content';
            }
        };

        self::assertSame('stringable-content', bytes($stringable));
    }

    public static function providesStringCases(): \Generator
    {
        yield ['HelloWorld', 'hello_world'];
        yield ['helloWorld', 'hello_world'];
        yield ['hello-world', 'hello_world'];
        yield ['hello_world', 'hello_world'];
        yield ['hello world', 'hello_world'];
        yield ['hello.world', 'hello_world'];
        yield ['XMLHttpRequest', 'xml_http_request'];
    }
}
