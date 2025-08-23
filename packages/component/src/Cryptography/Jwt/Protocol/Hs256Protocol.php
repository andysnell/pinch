<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\KeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\PublicKey;
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
 * HS256 (HMAC with SHA-256) JWT protocol implementation
 *
 * Security Note: Uses HMAC-SHA256 for signatures with shared secret keys
 */
final readonly class Hs256Protocol implements JwtProtocol
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function sign(KeyPair $keyPair, JwtHeader $header, JwtPayload $payload): Jwt
    {
        throw new JwtLogicException('HS256 protocol does not support asymmetric signing');
    }

    public function verify(PublicKey $publicKey, Jwt $token): DecodedJwtToken
    {
        throw new JwtLogicException('HS256 protocol does not support asymmetric verification');
    }

    public function signSymmetric(SharedKey $key, JwtHeader $header, JwtPayload $payload): Jwt
    {
        if ($header->algorithm !== JwtAlgorithm::HS256) {
            throw new JwtLogicException('Header algorithm must be HS256');
        }

        // Encode header and payload
        $headerJson = \json_encode($header, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
        $payloadJson = \json_encode($payload, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);

        $headerEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $headerJson);
        $payloadEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $payloadJson);

        // Create signing input
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        // Sign with HMAC-SHA256
        $signature = \hash_hmac('sha256', $signingInput, $key->bytes(), true);
        $signatureEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $signature);

        $token = $signingInput . '.' . $signatureEncoded;

        return new Jwt($token);
    }

    public function verifySymmetric(SharedKey $key, Jwt $token): DecodedJwtToken
    {
        $decoded = DecodedJwtToken::fromJwt($token, $this->clock);

        if ($decoded->header->algorithm !== JwtAlgorithm::HS256) {
            throw new InvalidJwtToken('Token algorithm is not HS256');
        }

        // Extract parts
        $parts = \explode('.', $token->value);
        if (\count($parts) !== 3) {
            throw new InvalidJwtToken('Invalid JWT format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        // Compute expected signature
        $expectedSignature = \hash_hmac('sha256', $signingInput, $key->bytes(), true);
        $expectedSignatureEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $expectedSignature);

        // Verify signature using constant-time comparison
        if (! \hash_equals($expectedSignatureEncoded, $signatureEncoded)) {
            throw new InvalidJwtToken('JWT signature verification failed');
        }

        return $decoded;
    }
}
