<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Filesystem;

use PhoneBurner\Pinch\Filesystem\FileReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\Pinch\UNIT_TEST_ROOT;

final class FileReaderTest extends TestCase
{
    #[Test]
    #[DataProvider('providesTestCases')]
    public function toStringReturnsFileContents(string|\Stringable $file): void
    {
        $reader = FileReader::make($file);
        self::assertStringEqualsFile(UNIT_TEST_ROOT . '/Fixtures/lorem.txt', (string)$reader);
    }

    #[Test]
    #[DataProvider('providesEmptyTestCases')]
    public function toStringReturnsFileContentsEmptyCase(string|\Stringable $file): void
    {
        $reader = FileReader::make($file);
        self::assertSame('', (string)$reader);
    }

    #[Test]
    #[DataProvider('providesTestCases')]
    public function iteratingReturnsFileContents(string|\Stringable $file): void
    {
        $reader = FileReader::make($file);
        self::assertStringEqualsFile(UNIT_TEST_ROOT . '/Fixtures/lorem.txt', \implode('', [...$reader]));
    }

    #[Test]
    #[DataProvider('providesEmptyTestCases')]
    public function iteratingReturnsFileContentsEmptyCase(string|\Stringable $file): void
    {
        $reader = FileReader::make($file);
        self::assertSame('', \implode('', [...$reader]));
    }

    #[Test]
    public function makeChecksIfFileExists(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File Not Readable:');
        FileReader::make(UNIT_TEST_ROOT . '/Fixtures/does-not-exist.txt');
    }

    #[Test]
    public function makeChecksIfFileIsReadable(): void
    {
        $temp_file = \sys_get_temp_dir() . '/pinch-unreadable-' . \random_int(100_000, 999_999);
        \file_put_contents($temp_file, 'content');
        \chmod($temp_file, 0000); // Make unreadable

        try {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('File Not Readable:');
            FileReader::make($temp_file);
        } finally {
            @\chmod($temp_file, 0644);
            @\unlink($temp_file);
        }
    }

    #[Test]
    public function makeNormalizesSplFileInfoPath(): void
    {
        $file_info = new \SplFileInfo(UNIT_TEST_ROOT . '/Fixtures/lorem.txt');
        $reader = FileReader::make($file_info);

        self::assertStringEqualsFile(UNIT_TEST_ROOT . '/Fixtures/lorem.txt', (string)$reader);
    }

    #[Test]
    public function linesIteratesOverFileLines(): void
    {
        $reader = FileReader::make(UNIT_TEST_ROOT . '/Fixtures/lorem.txt');
        $lines = [];

        foreach ($reader->lines() as $line) {
            $lines[] = $line;
        }

        self::assertNotEmpty($lines);
        self::assertIsString($lines[0]);
    }

    #[Test]
    public function linesHandlesEmptyFile(): void
    {
        $reader = FileReader::make(UNIT_TEST_ROOT . '/Fixtures/empty.txt');
        $lines = [];

        foreach ($reader->lines() as $line) {
            $lines[] = $line;
        }

        self::assertEmpty($lines);
    }

    public static function providesTestCases(): \Generator
    {
        yield [UNIT_TEST_ROOT . '/Fixtures/lorem.txt'];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return UNIT_TEST_ROOT . '/Fixtures/lorem.txt';
            }
        }];
        yield [new \SplFileInfo(UNIT_TEST_ROOT . '/Fixtures/lorem.txt')];
        yield [new \SplFileObject(UNIT_TEST_ROOT . '/Fixtures/lorem.txt', 'r+b')];
    }

    public static function providesEmptyTestCases(): \Generator
    {
        yield [UNIT_TEST_ROOT . '/Fixtures/empty.txt'];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return UNIT_TEST_ROOT . '/Fixtures/empty.txt';
            }
        }];
        yield [new \SplFileInfo(UNIT_TEST_ROOT . '/Fixtures/empty.txt')];
        yield [new \SplFileObject(UNIT_TEST_ROOT . '/Fixtures/empty.txt', 'r+b')];
    }
}
