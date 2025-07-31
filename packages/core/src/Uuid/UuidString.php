<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Uuid;

use Ramsey\Uuid\UuidInterface;

class UuidString implements UuidInterface
{
    use UuidStringWrapper;
}
