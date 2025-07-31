<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Fixtures;

final readonly class DotAccessTestStruct
{
    public function __construct(
        public string $needle,
        public bool $exists,
        public mixed $expected,
    ) {
    }
}
