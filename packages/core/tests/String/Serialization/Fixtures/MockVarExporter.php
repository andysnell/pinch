<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Serialization\Fixtures;

use PhoneBurner\Pinch\String\Serialization\VarExporter;

final class MockVarExporter implements VarExporter
{
    public function file(
        \Stringable|string $filename,
        mixed $value,
        string $header_message = 'Generated File',
        \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ): bool {
        $content = $this->string($value);
        $full_content = "<?php\n\n// {$header_message}\n// Generated: {$timestamp->format('Y-m-d H:i:s')}\n\nreturn {$content};\n";

        return \file_put_contents((string)$filename, $full_content) !== false;
    }

    public function string(mixed $value): string
    {
        return \var_export($value, true);
    }
}
