<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\RsaSignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\RsaSignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtHeader;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\JwtPayload;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationCompleted;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationFailed;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationStarted;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * RS256 (RSA with SHA-256) JWT protocol implementation
 *
 * Security Note: Uses RSA PKCS#1 v1.5 with SHA-256 for signatures
 */
final readonly class Rs256Protocol implements JwtProtocol
{
    public function __construct(
        private ClockInterface $clock,
        private EventDispatcherInterface|null $eventDispatcher = null,
    ) {
    }

    public function sign(SignatureKeyPair|RsaSignatureKeyPair $keyPair, JwtHeader $header, JwtPayload $payload): Jwt
    {
        if ($header->algorithm !== JwtAlgorithm::RS256) {
            throw new JwtLogicException('Header algorithm must be RS256');
        }

        // Security: Prevent algorithm confusion attacks by enforcing RSA key usage
        if (! ($keyPair instanceof RsaSignatureKeyPair)) {
            throw new JwtLogicException('RS256 algorithm requires RSA keys, but non-RSA key provided');
        }

        // Encode header and payload
        $headerJson = \json_encode($header, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
        $payloadJson = \json_encode($payload, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);

        $headerEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $headerJson);
        $payloadEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $payloadJson);

        // Create signing input
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        // Sign with RSA-SHA256
        $signature = $this->signData($signingInput, $keyPair);
        $signatureEncoded = ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $signature);

        $token = $signingInput . '.' . $signatureEncoded;

        return new Jwt($token);
    }

    public function verify(SignaturePublicKey|RsaSignaturePublicKey $publicKey, Jwt $token): DecodedJwtToken
    {
        $decoded = DecodedJwtToken::fromJwt($token, $this->clock);

        // Dispatch verification started event
        $this->eventDispatcher?->dispatch(new JwtVerificationStarted(
            jwt: $token,
            algorithm: JwtAlgorithm::RS256->value,
            keyId: $decoded->header->key_id,
        ));

        if ($decoded->header->algorithm !== JwtAlgorithm::RS256) {
            throw new InvalidJwtToken('Token algorithm is not RS256');
        }

        // Security: Prevent algorithm confusion attacks by enforcing RSA key usage
        if (! ($publicKey instanceof RsaSignaturePublicKey)) {
            throw new InvalidJwtToken('RS256 algorithm requires RSA keys, but non-RSA key provided');
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

        // Verify signature
        try {
            if (! $this->verifySignature($signingInput, $signature, $publicKey)) {
                $exception = new InvalidJwtToken('JWT signature verification failed');
                $this->eventDispatcher?->dispatch(new JwtVerificationFailed(
                    jwt: $token,
                    exception: $exception,
                    algorithm: JwtAlgorithm::RS256->value,
                    keyId: $decoded->header->key_id,
                    reason: 'signature_verification_failed',
                ));
                throw $exception;
            }
        } catch (\Throwable $e) {
            $this->eventDispatcher?->dispatch(new JwtVerificationFailed(
                jwt: $token,
                exception: $e,
                algorithm: JwtAlgorithm::RS256->value,
                keyId: $decoded->header->key_id,
                reason: 'signature_verification_error',
            ));
            throw $e;
        }

        // Dispatch verification completed event
        $this->eventDispatcher?->dispatch(new JwtVerificationCompleted(
            jwt: $token,
            decodedToken: $decoded,
            algorithm: JwtAlgorithm::RS256->value,
            keyId: $decoded->header->key_id,
        ));

        return $decoded;
    }

    public function signSymmetric(SharedKey $key, JwtHeader $header, JwtPayload $payload): Jwt
    {
        throw new JwtLogicException('RS256 protocol does not support symmetric signing');
    }

    public function verifySymmetric(SharedKey $key, Jwt $token): DecodedJwtToken
    {
        throw new JwtLogicException('RS256 protocol does not support symmetric verification');
    }

    private function signData(string $data, SignatureKeyPair|RsaSignatureKeyPair $keyPair): string
    {
        // Handle both RSA and Ed25519 keys for compatibility
        if ($keyPair instanceof RsaSignatureKeyPair) {
            // Use proper RSA-SHA256 signing
            $signature = '';
            if (! \openssl_sign($data, $signature, $keyPair->secret->openSslKey(), 'SHA256')) {
                throw new InvalidJwtToken('Failed to sign JWT with RSA key');
            }
            return $signature;
        }

        // Fallback to Ed25519 for existing tests
        return \sodium_crypto_sign_detached($data, $keyPair->secret->bytes());
    }

    private function verifySignature(
        string $data,
        string $signature,
        SignaturePublicKey|RsaSignaturePublicKey $publicKey,
    ): bool {
        try {
            // Handle both RSA and Ed25519 keys for compatibility
            if ($publicKey instanceof RsaSignaturePublicKey) {
                // Use proper RSA-SHA256 verification
                return \openssl_verify($data, $signature, $publicKey->openSslKey(), 'SHA256') === 1;
            }

            // Fallback to Ed25519 for existing tests
            if ($signature === '') {
                return false;
            }
            return \sodium_crypto_sign_verify_detached($signature, $data, $publicKey->bytes());
        } catch (\Throwable) {
            return false;
        }
    }
}
