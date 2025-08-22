<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJwksUri;

/**
 * URI value object for JWKS endpoints
 */
final readonly class JwksUri implements \Stringable
{
    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $uri): self
    {
        $parsed_uri = \parse_url($uri);

        if ($parsed_uri === false || ! isset($parsed_uri['scheme'], $parsed_uri['host'])) {
            throw InvalidJwksUri::fromInvalidUri($uri);
        }

        // Check for invalid characters
        if (! \filter_var($uri, \FILTER_VALIDATE_URL)) {
            throw InvalidJwksUri::fromInvalidUri($uri);
        }

        // Handle HTTP specifically vs other invalid schemes
        if ($parsed_uri['scheme'] === 'http') {
            throw InvalidJwksUri::fromNonHttpsUri($uri);
        }

        if ($parsed_uri['scheme'] !== 'https') {
            throw InvalidJwksUri::fromInvalidUri($uri);
        }

        return new self($uri);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
