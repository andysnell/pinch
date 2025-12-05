<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database\Redis\Config;

class RedisAuthenticationCredentials
{
    /**
     * @phpstan-assert non-empty-string|null $this->user
     * @phpstan-assert non-empty-string $this->pass
     */
    public function __construct(
        #[\SensitiveParameter] public string|null $user,
        #[\SensitiveParameter] public string $pass,
    ) {
        if ($this->user === '') {
            throw new \UnexpectedValueException('Redis authentication username must be null or non-empty-string');
        }

        if ($this->pass === '') {
            throw new \UnexpectedValueException('Redis authentication password must be non-empty-string');
        }
    }

    /**
     * @return ($assoc is true ? array{user: string|null, pass: string} : array{0: string|null, 1: string})
     */
    public function toArray(bool $assoc = true): array
    {
        return $assoc
            ? ['user' => $this->user, 'pass' => $this->pass]
            : [$this->user, $this->pass];
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(#[\SensitiveParameter] array $data): void
    {
        $this->__construct(...$data);
    }
}
