<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Exception;

use InvalidArgumentException;

final class InvalidJsonWebKey extends InvalidArgumentException implements JwksException
{
    public static function fromMissingKeyType(): self
    {
        return new self('JSON Web Key must have a "kty" (key type) property.');
    }

    public static function fromMissingKeyId(): self
    {
        return new self('JSON Web Key must have a "kid" (key ID) property.');
    }

    public static function fromMissingUse(): self
    {
        return new self('JSON Web Key must have a "use" property.');
    }

    public static function fromInvalidUse(string $use): self
    {
        return new self(\sprintf("Invalid key use '%s'. Must be 'sig' or 'enc'.", $use));
    }

    public static function fromMissingAlgorithm(): self
    {
        return new self('JSON Web Key must have an "alg" (algorithm) property.');
    }

    public static function fromInvalidKeyData(string $property): self
    {
        return new self(\sprintf("JSON Web Key is missing required property: '%s'.", $property));
    }
}
