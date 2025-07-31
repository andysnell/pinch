<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Utility;

class ResultCounter implements \Countable
{
    public function __construct(
        public int $success = 0,
        public int $warning = 0,
        public int $error = 0,
    ) {
    }

    #[\Override]
    public function count(): int
    {
        return \max($this->success + $this->warning + $this->error, 0);
    }
}
