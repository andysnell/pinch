<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Filesystem;

use PhoneBurner\Pinch\Exception\UnableToReadFile;
use PhoneBurner\Pinch\Exception\UnableToWriteFile;
use PhoneBurner\Pinch\Trait\HasNonInstantiableBehavior;

final readonly class File
{
    use HasNonInstantiableBehavior;

    public static function read(\Stringable|string $filename): string
    {
        return @\file_get_contents(self::filename($filename))
            ?: throw UnableToReadFile::atLocation((string)$filename);
    }

    public static function write(\Stringable|string $filename, string $content): int
    {
        return \file_put_contents(self::filename($filename), $content)
            ?: throw UnableToWriteFile::atLocation((string)$filename);
    }

    /**
     * @return resource stream
     */
    public static function open(
        \Stringable|string $filename,
        FileMode $mode = FileMode::Read,
        mixed $context = null,
    ): mixed {
        $context = match (true) {
            $context === null => null,
            \is_resource($context) && \get_resource_type($context) === 'stream-context' => $context,
            default => throw new \InvalidArgumentException('context must be null or stream-context resource'),
        };

        $stream = @\fopen(self::filename($filename), $mode->value, false, $context);
        if ($stream === false) {
            throw new \RuntimeException('Could Not Create Stream');
        }

        return $stream;
    }

    public static function size(mixed $value): int
    {
        return match (true) {
            \is_string($value) && \file_exists($value) => \filesize($value) ?: 0,
            \is_object($value) && \method_exists($value, 'getSize') => $value->getSize() ?: 0,
            \is_resource($value) && \get_resource_type($value) === 'stream' => \fstat($value)['size'] ?? null,
            default => throw new \InvalidArgumentException('Unsupported Type:' . \get_debug_type($value)),
        } ?? throw new \RuntimeException('Unable to Get Size of Stream');
    }

    public static function close(mixed $value): void
    {
        match (true) {
            \is_resource($value) && \get_resource_type($value) === 'stream' => \fclose($value),
            \is_object($value) && \method_exists($value, 'close') => $value->close(),
            default => null,
        };
    }

    public static function filename(\Stringable|string $filename): string
    {
        return match (true) {
            \is_string($filename) => $filename,
            $filename instanceof \SplFileInfo => $filename->getPathname(),
            default => (string)$filename,
        };
    }
}
