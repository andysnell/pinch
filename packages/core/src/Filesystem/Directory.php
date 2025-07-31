<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Filesystem;

class Directory
{
    private const int ITERATOR_FLAGS = \FilesystemIterator::SKIP_DOTS
    | \FilesystemIterator::CURRENT_AS_PATHNAME
    | \FilesystemIterator::FOLLOW_SYMLINKS;

    public static function recursive(string $directory): \Iterator
    {
        \assert(\is_dir($directory) && \is_readable($directory));
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, self::ITERATOR_FLAGS | \FilesystemIterator::SKIP_DOTS));
    }
}
