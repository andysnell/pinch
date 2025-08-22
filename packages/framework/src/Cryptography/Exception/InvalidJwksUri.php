<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Exception;

use InvalidArgumentException;

final class InvalidJwksUri extends InvalidArgumentException implements JwksException
{
    public static function fromInvalidUri(string $uri): self
    {
        return new self(\sprintf("Invalid JWKS URI: '%s'. Must be a valid HTTPS URL.", $uri));
    }

    public static function fromNonHttpsUri(string $uri): self
    {
        return new self(\sprintf("JWKS URI must use HTTPS for security: '%s'.", $uri));
    }
}
