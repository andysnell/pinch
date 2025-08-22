<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Exception;

use InvalidArgumentException;

final class InvalidJsonWebKeySet extends InvalidArgumentException implements JwksException
{
    public static function fromMissingKeys(): self
    {
        return new self('JWKS response must contain a "keys" array.');
    }

    public static function fromInvalidKeysStructure(): self
    {
        return new self('JWKS "keys" must be an array of objects.');
    }

    public static function fromEmptyKeySet(): self
    {
        return new self('JWKS key set cannot be empty.');
    }

    public static function fromInvalidJson(string $error): self
    {
        return new self('Invalid JSON in JWKS response: ' . $error);
    }
}
