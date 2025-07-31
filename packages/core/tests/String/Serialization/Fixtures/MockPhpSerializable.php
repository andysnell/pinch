<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\Serialization\Fixtures;

use PhoneBurner\Pinch\String\Serialization\PhpSerializable;

/**
 * @implements PhpSerializable<array{data: string}>
 */
final class MockPhpSerializable implements PhpSerializable
{
    public function __construct(private string $data)
    {
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function __serialize(): array
    {
        return ['data' => $this->data];
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data['data'];
    }
}
