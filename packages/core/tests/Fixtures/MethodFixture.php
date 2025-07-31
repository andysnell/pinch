<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Fixtures;

class MethodFixture
{
    public function methodWithParameters(mixed $first, mixed $second): void
    {
    }

    public function methodWithTypeHint(StringWrapper $logger): void
    {
    }

    public function methodWithDefaultValue(mixed $param = 'default'): void
    {
    }

    public function methodWithDefaultAndType(StringWrapper|null $logger = null): void
    {
    }

    public function methodWithUnionType(string|int $param): void
    {
    }

    public function methodWithBuiltinType(string $param): void
    {
    }

    public function methodWithSelfType(self $param): void
    {
    }
}
