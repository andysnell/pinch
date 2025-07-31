<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Filesystem;

use PhoneBurner\Pinch\Exception\UnableToCreateDirectory;
use PhoneBurner\Pinch\Exception\UnableToWriteFile;
use PhoneBurner\Pinch\Filesystem\FileWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileWriterTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        foreach ((array)\glob(__DIR__ . '/test.txt*') as $file) {
            @\unlink((string)$file);
        }
    }

    #[Test]
    public function stringWritesExpectedFile(): void
    {
        $contents = "foo\nbar\nbaz\n";
        $filename = __DIR__ . '/test.txt';
        self::assertFileDoesNotExist($filename);

        FileWriter::string($filename, $contents);

        self::assertFileExists($filename);
        self::assertSame([], \glob($filename . '.*'));
        self::assertSame($contents, (string)\file_get_contents($filename));
    }

    #[Test]
    public function stringOverwritesExpectedFile(): void
    {
        $old_contents = "qux\nquux\nquuz\n";
        $new_contents = "foo\nbar\nbaz\n";

        $filename = __DIR__ . '/test.txt';
        \file_put_contents($filename, $old_contents);
        self::assertFileExists($filename);
        self::assertSame($old_contents, (string)\file_get_contents($filename));

        FileWriter::string($filename, $new_contents);

        self::assertFileExists($filename);
        self::assertSame([], \glob($filename . '.*'));
        self::assertSame($new_contents, (string)\file_get_contents($filename));
    }

    #[Test]
    public function iterableWritesExpectedFile(): void
    {
        $iterable = static function (): \Generator {
            yield 'foo' . \PHP_EOL;
            yield 'bar' . \PHP_EOL;
            yield 'baz' . \PHP_EOL;
        };

        $filename = __DIR__ . '/test.txt';
        self::assertFileDoesNotExist($filename);

        FileWriter::iterable($filename, $iterable());

        self::assertFileExists($filename);
        self::assertSame([], \glob($filename . '.*'));
        self::assertSame("foo\nbar\nbaz\n", (string)\file_get_contents($filename));
    }

    #[Test]
    public function iterableOverwritesExpectedFile(): void
    {
        $old_contents = "qux\nquux\nquuz\n";
        $iterable = static function (): \Generator {
            yield 'foo' . \PHP_EOL;
            yield 'bar' . \PHP_EOL;
            yield 'baz' . \PHP_EOL;
        };

        $filename = __DIR__ . '/test.txt';
        \file_put_contents($filename, $old_contents);
        self::assertFileExists($filename);
        self::assertSame($old_contents, (string)\file_get_contents($filename));

        FileWriter::iterable($filename, $iterable());

        self::assertFileExists($filename);
        self::assertSame([], \glob($filename . '.*'));
        self::assertSame("foo\nbar\nbaz\n", (string)\file_get_contents($filename));
    }

    #[Test]
    public function stringHandlesSplFileInfo(): void
    {
        $filename = __DIR__ . '/test.txt';
        $file_info = new \SplFileInfo($filename);
        $contents = 'SplFileInfo content';

        FileWriter::string($file_info, $contents);

        self::assertFileExists($filename);
        self::assertSame($contents, (string)\file_get_contents($filename));
    }

    #[Test]
    public function stringHandlesStringableFilename(): void
    {
        $filename = __DIR__ . '/test.txt';
        $stringable_filename = new readonly class ($filename) implements \Stringable {
            public function __construct(private string $path)
            {
            }

            public function __toString(): string
            {
                return $this->path;
            }
        };
        $contents = 'Stringable filename content';

        FileWriter::string($stringable_filename, $contents);

        self::assertFileExists($filename);
        self::assertSame($contents, (string)\file_get_contents($filename));
    }

    #[Test]
    public function stringHandlesStringableContents(): void
    {
        $filename = __DIR__ . '/test.txt';
        $stringable_contents = new readonly class ('Stringable contents') implements \Stringable {
            public function __construct(private string $content)
            {
            }

            public function __toString(): string
            {
                return $this->content;
            }
        };

        FileWriter::string($filename, $stringable_contents);

        self::assertFileExists($filename);
        self::assertSame('Stringable contents', (string)\file_get_contents($filename));
    }

    #[Test]
    public function iterableHandlesNestedIterables(): void
    {
        $nested_iterable = static function (): \Generator {
            yield ['foo', 'bar'];
            yield 'baz';
            yield ['qux', 'quux'];
        };

        $filename = __DIR__ . '/test.txt';

        FileWriter::iterable($filename, $nested_iterable());

        self::assertFileExists($filename);
        self::assertSame('foobarbazquxquux', (string)\file_get_contents($filename));
    }

    #[Test]
    public function iterableHandlesSplFileInfoFilename(): void
    {
        $filename = __DIR__ . '/test.txt';
        $file_info = new \SplFileInfo($filename);
        $iterable = static function (): \Generator {
            yield 'SplFileInfo';
            yield ' iterable';
        };

        FileWriter::iterable($file_info, $iterable());

        self::assertFileExists($filename);
        self::assertSame('SplFileInfo iterable', (string)\file_get_contents($filename));
    }

    #[Test]
    public function stringThrowsWhenFileExistsButIsNotRegularFile(): void
    {
        $directory_as_file = __DIR__ . '/test.txt';
        \mkdir($directory_as_file);

        try {
            $this->expectException(UnableToWriteFile::class);
            $this->expectExceptionMessage('The location exists and is writable, but is not a regular file.');

            FileWriter::string($directory_as_file, 'content');
        } finally {
            @\rmdir($directory_as_file);
        }
    }

    #[Test]
    public function stringThrowsWhenFileExistsButIsNotWritable(): void
    {
        $filename = __DIR__ . '/readonly-test.txt';
        \file_put_contents($filename, 'content');
        \chmod($filename, 0444); // Read-only

        try {
            $this->expectException(UnableToWriteFile::class);
            $this->expectExceptionMessage('The file already exists, but it is not writable.');

            FileWriter::string($filename, 'new content');
        } finally {
            @\chmod($filename, 0644);
            @\unlink($filename);
        }
    }

    #[Test]
    public function stringThrowsWhenDirectoryIsNotWritable(): void
    {
        $readonly_dir = __DIR__ . '/readonly-dir-' . \random_int(100_000, 999_999);
        $filename = $readonly_dir . '/test.txt';

        \mkdir($readonly_dir);
        \chmod($readonly_dir, 0444); // Read-only directory

        try {
            $this->expectException(UnableToCreateDirectory::class);
            $this->expectExceptionMessage('The directory already exists, but it is not writable.');

            FileWriter::string($filename, 'content');
        } finally {
            @\chmod($readonly_dir, 0755);
            @\rmdir($readonly_dir);
        }
    }

    #[Test]
    public function stringThrowsWhenDirectoryExistsButIsNotDirectory(): void
    {
        $file_as_dir = __DIR__ . '/file-as-dir-' . \random_int(100_000, 999_999);
        $filename = $file_as_dir . '/test.txt';

        \file_put_contents($file_as_dir, 'not a directory');

        try {
            $this->expectException(UnableToCreateDirectory::class);
            $this->expectExceptionMessage('The location already exists, but is not a directory.');

            FileWriter::string($filename, 'content');
        } finally {
            @\unlink($file_as_dir);
        }
    }

    #[Test]
    public function stringCreatesDirectoryRecursively(): void
    {
        $nested_dir = __DIR__ . '/deep/nested/directory';
        $filename = $nested_dir . '/test.txt';

        FileWriter::string($filename, 'deep content');

        self::assertFileExists($filename);
        self::assertSame('deep content', (string)\file_get_contents($filename));

        // Cleanup
        @\unlink($filename);
        @\rmdir($nested_dir);
        @\rmdir(\dirname($nested_dir));
        @\rmdir(\dirname($nested_dir, 2));
    }

    #[Test]
    public function iterableThrowsWhenFileExistsButIsNotRegularFile(): void
    {
        $directory_as_file = __DIR__ . '/test.txt';
        \mkdir($directory_as_file);

        try {
            $this->expectException(UnableToWriteFile::class);
            $this->expectExceptionMessage('The location exists and is writable, but is not a regular file.');

            FileWriter::iterable($directory_as_file, ['content']);
        } finally {
            @\rmdir($directory_as_file);
        }
    }
}
