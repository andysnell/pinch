<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;

/**
 * JWT token with decoded header and payload claims
 *
 * Mirrors the PasetoWithClaims pattern
 */
final readonly class JwtWithClaims implements \Stringable
{
    public function __construct(
        public Jwt $token,
        public JwtHeader $header,
        public JwtPayload $payload,
    ) {
    }

    public function __toString(): string
    {
        return $this->token->value;
    }
}
