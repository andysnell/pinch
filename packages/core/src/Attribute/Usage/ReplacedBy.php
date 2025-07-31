<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute\Usage;

#[\Attribute]
final readonly class ReplacedBy
{
    public \DateTimeImmutable|null $since;

    public function __construct(
        public string $replacement,
        public string $note = '',
        \DateTimeInterface|string|null $since = null,
    ) {
        $this->since = match (true) {
            $since === null => null,
            $since instanceof \DateTimeInterface => \DateTimeImmutable::createFromInterface($since),
            default => new \DateTimeImmutable($since),
        };
    }
}
