<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Filesystem;

use PhoneBurner\Pinch\Filesystem\Directory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DirectoryTest extends TestCase
{
    private string $temp_dir;

    protected function setUp(): void
    {
        $this->temp_dir = \sys_get_temp_dir() . '/pinch-directory-test-' . \random_int(100_000, 999_999);
        $nested_dir = $this->temp_dir . '/nested/deeper';
        $test_file = $nested_dir . '/test.txt';

        \mkdir($nested_dir, 0777, true);
        \file_put_contents($test_file, 'test content');
        \file_put_contents($this->temp_dir . '/root-file.txt', 'root content');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->temp_dir);
    }

    #[Test]
    public function recursiveReturnsIteratorForValidDirectory(): void
    {
        $iterator = Directory::recursive($this->temp_dir);

        self::assertInstanceOf(\Iterator::class, $iterator);
        self::assertInstanceOf(\RecursiveIteratorIterator::class, $iterator);
    }

    #[Test]
    public function recursiveFindsAllFilesRecursively(): void
    {
        $iterator = Directory::recursive($this->temp_dir);
        $files = [];

        foreach ($iterator as $file) {
            $files[] = $file;
        }

        $file_basenames = \array_map('basename', $files);

        self::assertContains('test.txt', $file_basenames);
        self::assertContains('root-file.txt', $file_basenames);
        self::assertGreaterThanOrEqual(2, \count($files));
    }

    #[Test]
    public function recursiveIncludesHiddenDirectories(): void
    {
        // Create a .hidden directory
        $hidden_dir = $this->temp_dir . '/.hidden';
        \mkdir($hidden_dir, 0777, true);
        \file_put_contents($hidden_dir . '/hidden-file.txt', 'hidden content');

        $iterator = Directory::recursive($this->temp_dir);
        $files = [];

        foreach ($iterator as $file) {
            $files[] = $file;
        }

        $file_paths = \implode('|', $files);

        // RecursiveDirectoryIterator with SKIP_DOTS skips '.' and '..' but includes hidden files/directories
        self::assertStringContainsString('.hidden', $file_paths);
        self::assertStringContainsString('hidden-file.txt', $file_paths);
    }

    #[Test]
    public function recursiveReturnsPathnames(): void
    {
        $iterator = Directory::recursive($this->temp_dir);

        foreach ($iterator as $file) {
            self::assertIsString($file);
            self::assertFileExists($file);
            break; // Just test the first item
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (! \is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                @\rmdir($file->getRealPath());
            } else {
                @\unlink($file->getRealPath());
            }
        }

        @\rmdir($dir);
    }
}
