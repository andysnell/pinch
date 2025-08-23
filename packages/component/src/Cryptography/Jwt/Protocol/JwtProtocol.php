<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\KeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\PublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;

/**
 * Base interface for JWT protocol implementations
 */
interface JwtProtocol
{
    /**
     * Sign a JWT token with asymmetric key
     */
    public function sign(KeyPair $keyPair, JwtHeader $header, JwtPayload $payload): Jwt;

    /**
     * Verify a JWT token with public key
     */
    public function verify(PublicKey $publicKey, Jwt $token): DecodedJwtToken;

    /**
     * Sign a JWT token with symmetric key (HMAC)
     */
    public function signSymmetric(SharedKey $key, JwtHeader $header, JwtPayload $payload): Jwt;

    /**
     * Verify a JWT token with symmetric key (HMAC)
     */
    public function verifySymmetric(SharedKey $key, Jwt $token): DecodedJwtToken;
}
