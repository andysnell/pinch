<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Memory;

use PhoneBurner\Pinch\Memory\Bytes;
use PhoneBurner\Pinch\Memory\Unit\BinaryMemoryUnit;
use PhoneBurner\Pinch\Memory\Unit\DecimalMemoryUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(Bytes::class)]
final class BytesTest extends TestCase
{
    #[Test]
    public function constructWithNonNegativeValue(): void
    {
        $bytes = new Bytes(1024);
        self::assertSame(1024, $bytes->value);

        $bytes_zero = new Bytes(0);
        self::assertSame(0, $bytes_zero->value);
    }

    #[Test]
    public function constructThrowsForNegativeValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Bytes must be non-negative integer');
        new Bytes(-1);
    }

    #[Test]
    public function bitsReturnsCorrectValue(): void
    {
        $bytes = new Bytes(1);
        self::assertSame(8, $bytes->bits());

        $bytes_large = new Bytes(1024);
        self::assertSame(8192, $bytes_large->bits());

        $bytes_zero = new Bytes(0);
        self::assertSame(0, $bytes_zero->bits());
    }

    #[Test]
    #[DataProvider('provideConversionValues')]
    public function convertReturnsCorrectlyFormattedValue(
        int $byte_value,
        BinaryMemoryUnit|DecimalMemoryUnit $unit,
        int $precision,
        float $expected_value,
    ): void {
        $bytes = new Bytes($byte_value);
        self::assertSame($expected_value, $bytes->convert($unit, $precision));
    }

    public static function provideConversionValues(): \Generator
    {
        yield 'Bytes to KiB (default precision)' => [1536, BinaryMemoryUnit::Kibibyte, 2, 1.5];
        yield 'Bytes to MiB (default precision)' => [1_572_864, BinaryMemoryUnit::Mebibyte, 2, 1.5];
        yield 'Bytes to MiB (0 precision)' => [1_572_864, BinaryMemoryUnit::Mebibyte, 0, 2.0]; // round(1.5)
        yield 'Bytes to GiB (4 precision)' => [1_610_612_736, BinaryMemoryUnit::Gibibyte, 4, 1.5000];
        yield 'Bytes to KB (default precision)' => [1500, DecimalMemoryUnit::Kilobyte, 2, 1.5];
        yield 'Bytes to MB (1 precision)' => [1_500_000, DecimalMemoryUnit::Megabyte, 1, 1.5];
        yield 'Zero Bytes to KiB' => [0, BinaryMemoryUnit::Kibibyte, 2, 0.0];
        yield 'Default Unit (MiB)' => [(int)(2.5 * BinaryMemoryUnit::Mebibyte->value), BinaryMemoryUnit::Mebibyte, 2, 2.50];
    }

    #[Test]
    public function jsonSerializeReturnsIntegerValue(): void
    {
        $bytes = new Bytes(512);
        self::assertSame(512, $bytes->jsonSerialize());
    }

    #[Test]
    #[DataProvider('provideToStringValues')]
    public function toStringReturnsFormattedStringWithBestFitBinaryUnit(
        int $value,
        string $expected_string,
    ): void {
        $bytes = new Bytes($value);
        self::assertSame($expected_string, (string)$bytes);
    }

    public static function provideToStringValues(): \Generator
    {
        yield 'Zero Bytes' => [0, '0.00 B'];
        yield '500 Bytes' => [500, '500.00 B'];
        yield '1024 Bytes (1 KiB)' => [1024, '1.00 KiB'];
        yield '1536 Bytes (1.5 KiB)' => [1536, '1.50 KiB'];
        yield '1 MiB' => [1024 ** 2, '1.00 MiB'];
        yield '1.25 MiB' => [(int)(1.25 * (1024 ** 2)), '1.25 MiB'];
        yield 'Large Value (GiB)' => [(int)(3.7 * (1024 ** 3)), '3.70 GiB'];
    }

    #[Test]
    public function diffReturnsExpectedBytes(): void
    {
        $bytes1 = new Bytes(1024);
        $bytes2 = new Bytes(500);
        $diff = $bytes1->diff($bytes2);

        self::assertSame(524, $diff->value);
    }

    #[Test]
    public function diffThrowsWhenResultWouldBeNegative(): void
    {
        $bytes1 = new Bytes(500);
        $bytes2 = new Bytes(1024);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Bytes must be non-negative integer');
        $bytes1->diff($bytes2);
    }

    #[Test]
    public function diffWithZeroValues(): void
    {
        $bytes1 = new Bytes(1024);
        $bytes2 = new Bytes(0);
        $diff = $bytes1->diff($bytes2);

        self::assertSame(1024, $diff->value);

        $bytes3 = new Bytes(0);
        $bytes4 = new Bytes(1024);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Bytes must be non-negative integer');
        $bytes3->diff($bytes4);
    }

    #[Test]
    public function diffWithSameValues(): void
    {
        $bytes1 = new Bytes(1024);
        $bytes2 = new Bytes(1024);
        $diff = $bytes1->diff($bytes2);

        self::assertSame(0, $diff->value);
    }

    #[Test]
    public function convertWithMaximumPrecision(): void
    {
        $bytes = new Bytes(1536); // 1.5 KiB
        $result = $bytes->convert(BinaryMemoryUnit::Kibibyte, 10);
        self::assertSame(1.5, $result);
    }

    #[Test]
    public function convertWithZeroPrecisionRoundsCorrectly(): void
    {
        $bytes = new Bytes(1536); // 1.5 KiB
        $result = $bytes->convert(BinaryMemoryUnit::Kibibyte, 0);
        self::assertSame(2.0, $result); // round(1.5) = 2
    }

    #[Test]
    public function convertLargeValuesWithDecimalUnits(): void
    {
        $large_bytes = new Bytes(1_000_000_000_000); // 1 TB in decimal
        $result = $large_bytes->convert(DecimalMemoryUnit::Terabyte, 2);
        self::assertSame(1.0, $result);
    }

    #[Test]
    public function toStringWithLargeValues(): void
    {
        $large_bytes = new Bytes(1024 ** 4); // 1 TiB
        self::assertSame('1.00 TiB', (string)$large_bytes);

        $very_large_bytes = new Bytes(1024 ** 5); // 1 PiB
        self::assertSame('1.00 PiB', (string)$very_large_bytes);

        $extremely_large_bytes = new Bytes(1024 ** 6); // 1 EiB
        self::assertSame('1.00 EiB', (string)$extremely_large_bytes);
    }

    #[Test]
    public function toStringWithFractionalValues(): void
    {
        $bytes = new Bytes((int)(2.75 * 1024 ** 2)); // 2.75 MiB
        self::assertSame('2.75 MiB', (string)$bytes);

        $bytes2 = new Bytes((int)(0.5 * 1024)); // 0.5 KiB = 512 B
        self::assertSame('512.00 B', (string)$bytes2);
    }

    #[Test]
    public function convertUsesDefaultParametersCorrectly(): void
    {
        $bytes = new Bytes(1024 ** 2); // 1 MiB
        $result = $bytes->convert(); // Should use default BinaryMemoryUnit::Mebibyte, precision 2
        self::assertSame(1.0, $result);
    }

    #[Test]
    public function bitsCalculationForLargeValues(): void
    {
        $bytes = new Bytes(1024);
        self::assertSame(8192, $bytes->bits());

        $large_bytes = new Bytes(1000000);
        self::assertSame(8000000, $large_bytes->bits());
    }

    #[Test]
    public function jsonSerializeWithLargeValues(): void
    {
        $large_bytes = new Bytes(\PHP_INT_MAX);
        self::assertSame(\PHP_INT_MAX, $large_bytes->jsonSerialize());

        $bytes = new Bytes(0);
        self::assertSame(0, $bytes->jsonSerialize());
    }
}
