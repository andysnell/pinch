<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\Serialization;

interface VarExporter
{
    /**
     * Exports a PHP value to a file, adding the PHP opening tag, a header message,
     * and a timestamp before returning the value. Including the file should return
     * the same value as the original.
     */
    public function file(
        \Stringable|string $filename,
        mixed $value,
        string $header_message = 'Generated File',
        \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ): bool;

    public function string(mixed $value): string;
}
