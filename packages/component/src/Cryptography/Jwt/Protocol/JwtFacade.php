<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\EdDsaProtocol;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\Hs256Protocol;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\Rs256Protocol;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use Psr\Clock\ClockInterface;

/**
 * JWT (JSON Web Tokens) Facade
 *
 * High-level interface for JWT operations with security-first approach.
 *
 * Security Notes:
 * - Only supports secure algorithms (RS256, HS256, EdDSA)
 * - Validates time claims (exp, iat, nbf) automatically
 * - Rejects tokens with "none" algorithm
 */
final readonly class JwtFacade
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    /**
     * Sign a JWT with asymmetric signature
     */
    public function sign(SignatureKeyPair $keyPair, JwtPayload $payload, string|null $keyId = null): Jwt
    {
        $algorithm = $this->determineAsymmetricAlgorithm();
        $header = new JwtHeader($algorithm, key_id: $keyId);

        return $this->getProtocol($algorithm)->sign($keyPair, $header, $payload);
    }

    /**
     * Sign a JWT with symmetric signature (HMAC)
     */
    public function signSymmetric(SharedKey $key, JwtPayload $payload, string|null $keyId = null): Jwt
    {
        $header = new JwtHeader(JwtAlgorithm::HS256, key_id: $keyId);

        return $this->getProtocol(JwtAlgorithm::HS256)->signSymmetric($key, $header, $payload);
    }

    /**
     * Verify a JWT with public key and validate claims
     */
    public function verify(SignaturePublicKey $publicKey, Jwt $token): DecodedJwtToken
    {
        $decoded = DecodedJwtToken::fromJwt($token, $this->clock);

        // Validate time claims first
        $decoded->payload->validateTimeClaims();

        // Verify signature
        $protocol = $this->getProtocol($decoded->header->algorithm);
        return $protocol->verify($publicKey, $token);
    }

    /**
     * Verify a JWT with symmetric key and validate claims
     */
    public function verifySymmetric(SharedKey $key, Jwt $token): DecodedJwtToken
    {
        $decoded = DecodedJwtToken::fromJwt($token, $this->clock);

        // Validate time claims first
        $decoded->payload->validateTimeClaims();

        // Verify signature
        $protocol = $this->getProtocol($decoded->header->algorithm);
        return $protocol->verifySymmetric($key, $token);
    }

    private function determineAsymmetricAlgorithm(): JwtAlgorithm
    {
        // TODO: Determine algorithm based on key type
        // For now, default to RS256 - this will be implemented when we add the protocol classes
        return JwtAlgorithm::RS256;
    }

    private function getProtocol(JwtAlgorithm $algorithm): JwtProtocol
    {
        return match ($algorithm) {
            JwtAlgorithm::RS256 => new Rs256Protocol($this->clock),
            JwtAlgorithm::HS256 => new Hs256Protocol($this->clock),
            JwtAlgorithm::EdDSA => new EdDsaProtocol($this->clock),
        };
    }
}
