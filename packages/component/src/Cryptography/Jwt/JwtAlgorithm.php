<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt;

/**
 * Supported JWT signing algorithms
 *
 * Security Note: Only secure algorithms are supported.
 * "none" algorithm is explicitly rejected for security.
 */
enum JwtAlgorithm: string
{
    case RS256 = 'RS256'; // RSA with SHA-256
    case HS256 = 'HS256'; // HMAC with SHA-256
    case EdDSA = 'EdDSA'; // EdDSA signature algorithms (Ed25519)
}
