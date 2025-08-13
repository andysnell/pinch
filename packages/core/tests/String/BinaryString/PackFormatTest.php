<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\BinaryString;

use PhoneBurner\Pinch\String\BinaryString\PackFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PackFormat::class)]
final class PackFormatTest extends TestCase
{
    #[Test]
    #[DataProvider('providesPackFormatConstants')]
    public function packFormatConstantsAreCorrect(string $constant_name, string $expected_value): void
    {
        $reflection = new \ReflectionClass(PackFormat::class);
        $constant = $reflection->getConstant($constant_name);

        self::assertSame($expected_value, $constant);
    }

    public static function providesPackFormatConstants(): \Generator
    {
        yield 'STRING_NULL_PADDED' => ['STRING_NULL_PADDED', 'a'];
        yield 'STRING_NULL_TRIMMED' => ['STRING_NULL_TRIMMED', 'Z'];
        yield 'STRING_SPACE_PADDED' => ['STRING_SPACE_PADDED', 'A'];
        yield 'HEX_LOW' => ['HEX_LOW', 'h'];
        yield 'HEX_HIGH' => ['HEX_HIGH', 'H'];
        yield 'CHAR_SIGNED' => ['CHAR_SIGNED', 'c'];
        yield 'CHAR_UNSIGNED' => ['CHAR_UNSIGNED', 'C'];
        yield 'INT_SIGNED_ME' => ['INT_SIGNED_ME', 'i'];
        yield 'INT_UNSIGNED_ME' => ['INT_UNSIGNED_ME', 'I'];
        yield 'INT16_SIGNED_ME' => ['INT16_SIGNED_ME', 's'];
        yield 'INT16_UNSIGNED_ME' => ['INT16_UNSIGNED_ME', 'S'];
        yield 'INT16_UNSIGNED_BE' => ['INT16_UNSIGNED_BE', 'n'];
        yield 'INT16_UNSIGNED_LE' => ['INT16_UNSIGNED_LE', 'v'];
        yield 'INT32_SIGNED_ME' => ['INT32_SIGNED_ME', 'l'];
        yield 'INT32_UNSIGNED_ME' => ['INT32_UNSIGNED_ME', 'L'];
        yield 'INT32_UNSIGNED_BE' => ['INT32_UNSIGNED_BE', 'N'];
        yield 'INT32_UNSIGNED_LE' => ['INT32_UNSIGNED_LE', 'V'];
        yield 'INT64_SIGNED_ME' => ['INT64_SIGNED_ME', 'q'];
        yield 'INT64_UNSIGNED_ME' => ['INT64_UNSIGNED_ME', 'Q'];
        yield 'INT64_UNSIGNED_BE' => ['INT64_UNSIGNED_BE', 'J'];
        yield 'INT64_UNSIGNED_LE' => ['INT64_UNSIGNED_LE', 'P'];
        yield 'FLOAT_ME' => ['FLOAT_ME', 'f'];
        yield 'FLOAT_LE' => ['FLOAT_LE', 'g'];
        yield 'FLOAT_BE' => ['FLOAT_BE', 'G'];
        yield 'DOUBLE_ME' => ['DOUBLE_ME', 'd'];
        yield 'DOUBLE_LE' => ['DOUBLE_LE', 'e'];
        yield 'DOUBLE_BE' => ['DOUBLE_BE', 'E'];
        yield 'NULL_BYTE' => ['NULL_BYTE', 'x'];
        yield 'BACK_UP' => ['BACK_UP', 'X'];
        yield 'NULL_FILL' => ['NULL_FILL', '@'];
        yield 'REPEAT_TO_END' => ['REPEAT_TO_END', '*'];
    }
}
