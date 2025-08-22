<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use Psr\Clock\ClockInterface;

/**
 * EdDSA (Ed25519) JWT protocol implementation
 *
 * Security Note: Uses Ed25519 elliptic curve signatures via libsodium
 */
final readonly class EdDsaProtocol implements JwtProtocol
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function sign(SignatureKeyPair $keyPair, JwtHeader $header, JwtPayload $payload): Jwt
    {
        if ($header->algorithm !== JwtAlgorithm::EdDSA) {
            throw new JwtLogicException('Header algorithm must be EdDSA');
        }

        // Encode header and payload
        $headerJson = \json_encode($header, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
        $payloadJson = \json_encode($payload, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);

        $headerEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $headerJson);
        $payloadEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $payloadJson);

        // Create signing input
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        // Sign with Ed25519
        $signature = \sodium_crypto_sign_detached($signingInput, $keyPair->secret->bytes());
        $signatureEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $signature);

        $token = $signingInput . '.' . $signatureEncoded;

        return new Jwt($token);
    }

    public function verify(SignaturePublicKey $publicKey, Jwt $token): DecodedJwtToken
    {
        $decoded = DecodedJwtToken::fromJwt($token, $this->clock);

        if ($decoded->header->algorithm !== JwtAlgorithm::EdDSA) {
            throw new InvalidJwtToken('Token algorithm is not EdDSA');
        }

        // Extract parts
        $parts = \explode('.', $token->value);
        if (\count($parts) !== 3) {
            throw new InvalidJwtToken('Invalid JWT format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        // Decode signature
        $signature = ConstantTimeEncoder::decode(Encoding::Base64UrlNoPadding, $signatureEncoded);

        if ($signature === '') {
            throw new InvalidJwtToken('JWT signature cannot be empty');
        }

        // Verify signature with Ed25519
        if (! \sodium_crypto_sign_verify_detached($signature, $signingInput, $publicKey->bytes())) {
            throw new InvalidJwtToken('JWT signature verification failed');
        }

        return $decoded;
    }

    public function signSymmetric(SharedKey $key, JwtHeader $header, JwtPayload $payload): Jwt
    {
        throw new JwtLogicException('EdDSA protocol does not support symmetric signing');
    }

    public function verifySymmetric(SharedKey $key, Jwt $token): DecodedJwtToken
    {
        throw new JwtLogicException('EdDSA protocol does not support symmetric verification');
    }
}
