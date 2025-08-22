<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwt;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\RsaSignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims\DecodedJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationCompleted;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationFailed;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Event\JwtVerificationStarted;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Protocol\Rs256Protocol;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JsonWebKey;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksResolver;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksUri;
use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchCompleted;
use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchFailed;
use PhoneBurner\Pinch\Framework\Cryptography\Jwt\Event\JwksFetchStarted;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * JWT verifier that uses JWKS for key resolution
 *
 * Security Note: Validates JWT signatures using remotely fetched public keys
 */
final readonly class JwksJwtVerifier
{
    public function __construct(
        private JwksResolver $jwksResolver,
        private ClockInterface $clock,
        private LoggerInterface $logger = new NullLogger(),
        private EventDispatcherInterface|null $eventDispatcher = null,
    ) {
    }

    /**
     * Verify JWT using JWKS for key resolution
     *
     * @throws InvalidJwtToken when verification fails
     */
    public function verify(Jwt $jwt, JwksUri $jwksUri): DecodedJwtToken
    {
        $this->logger->debug('Verifying JWT with JWKS', ['jwks_uri' => $jwksUri->toString()]);

        // Decode JWT without verification to get header
        $decoded = DecodedJwtToken::fromJwt($jwt, $this->clock);

        // Dispatch JWT verification started event
        $this->eventDispatcher?->dispatch(new JwtVerificationStarted(
            jwt: $jwt,
            algorithm: $decoded->header->algorithm->value,
            keyId: $decoded->header->key_id,
        ));

        // Get key ID from header
        $keyId = $decoded->header->key_id;
        if ($keyId === null) {
            throw new InvalidJwtToken('JWT header missing required "kid" (Key ID) claim');
        }

        $this->logger->debug('JWT header contains key ID', [
            'key_id' => $keyId,
            'algorithm' => $decoded->header->algorithm->value,
        ]);

        // Dispatch JWKS fetch started event
        $this->eventDispatcher?->dispatch(new JwksFetchStarted(
            jwksUri: $jwksUri->toString(),
            keyId: $keyId,
        ));

        // Find the corresponding key in JWKS
        try {
            $jwks = $this->jwksResolver->resolve($jwksUri);

            // Dispatch JWKS fetch completed event
            $this->eventDispatcher?->dispatch(new JwksFetchCompleted(
                jwksUri: $jwksUri->toString(),
                keyCount: $jwks->count(),
                fromCache: false, // TODO: Track cache hits
                keyId: $keyId,
            ));

            $jwk = $jwks->findByKeyId($keyId);
            if ($jwk === null) {
                $exception = new InvalidJwtToken(\sprintf('Key with ID "%s" not found in JWKS', $keyId));
                $this->eventDispatcher?->dispatch(new JwtVerificationFailed(
                    jwt: $jwt,
                    exception: $exception,
                    algorithm: $decoded->header->algorithm->value,
                    keyId: $keyId,
                    reason: 'key_not_found_in_jwks',
                ));
                throw $exception;
            }
        } catch (\Throwable $e) {
            if (! ($e instanceof InvalidJwtToken)) {
                $this->eventDispatcher?->dispatch(new JwksFetchFailed(
                    jwksUri: $jwksUri->toString(),
                    exception: $e,
                    keyId: $keyId,
                    reason: 'jwks_fetch_error',
                ));
            }
            throw $e;
        }

        $this->logger->debug('Found matching key in JWKS', [
            'key_id' => $keyId,
            'key_type' => $jwk->key_type,
            'algorithm' => $jwk->algorithm,
        ]);

        // Verify algorithm matches
        $expectedAlgorithm = $this->mapJwkAlgorithmToJwt($jwk->algorithm);
        if ($decoded->header->algorithm !== $expectedAlgorithm) {
            throw new InvalidJwtToken(\sprintf(
                'Algorithm mismatch: JWT header specifies %s, but key supports %s',
                $decoded->header->algorithm->value,
                $jwk->algorithm,
            ));
        }

        // Convert JWK to public key and verify
        try {
            $publicKey = $this->jwkToPublicKey($jwk);
            $result = $this->verifyWithPublicKey($jwt, $publicKey, $decoded->header->algorithm);

            // Dispatch JWT verification completed event
            $this->eventDispatcher?->dispatch(new JwtVerificationCompleted(
                jwt: $jwt,
                decodedToken: $result,
                algorithm: $decoded->header->algorithm->value,
                keyId: $keyId,
            ));

            return $result;
        } catch (\Throwable $e) {
            $this->eventDispatcher?->dispatch(new JwtVerificationFailed(
                jwt: $jwt,
                exception: $e,
                algorithm: $decoded->header->algorithm->value,
                keyId: $keyId,
                reason: 'verification_failed',
            ));
            throw $e;
        }
    }

    /**
     * Convert JWK to appropriate public key object
     */
    private function jwkToPublicKey(JsonWebKey $jwk): RsaSignaturePublicKey
    {
        if ($jwk->key_type !== 'RSA') {
            throw new InvalidJwtToken(\sprintf('Unsupported key type: %s', $jwk->key_type));
        }

        if ($jwk->algorithm !== 'RS256') {
            throw new InvalidJwtToken(\sprintf('Unsupported algorithm: %s', $jwk->algorithm));
        }

        // Convert JWK to PEM format
        $pem = $this->convertRsaJwkToPem($jwk);

        return new RsaSignaturePublicKey($pem);
    }

    /**
     * Convert RSA JWK to PEM format
     */
    private function convertRsaJwkToPem(JsonWebKey $jwk): string
    {
        $keyData = $jwk->toArray();

        if (! isset($keyData['n']) || ! isset($keyData['e'])) {
            throw new InvalidJwtToken('Invalid RSA key: missing modulus (n) or exponent (e)');
        }

        // Decode base64url encoded RSA components
        $n = $this->base64UrlDecode($keyData['n']);
        $e = $this->base64UrlDecode($keyData['e']);

        // Build ASN.1 DER encoding for RSA public key
        $modulus = $this->encodeInteger($n);
        $exponent = $this->encodeInteger($e);

        // RSA public key sequence
        $sequence = $this->encodeSequence($modulus . $exponent);

        // Algorithm identifier for RSA
        $algorithmIdentifier = $this->encodeSequence(
            $this->encodeObjectIdentifier('1.2.840.113549.1.1.1') . // RSA OID
            $this->encodeNull(),
        );

        // SubjectPublicKeyInfo
        $subjectPublicKeyInfo = $this->encodeSequence(
            $algorithmIdentifier .
            $this->encodeBitString($sequence),
        );

        // Convert to PEM format
        $base64 = \base64_encode($subjectPublicKeyInfo);
        $pem = "-----BEGIN PUBLIC KEY-----\n";
        $pem .= \chunk_split($base64, 64, "\n");

        return $pem . "-----END PUBLIC KEY-----";
    }

    /**
     * Verify JWT with public key using appropriate protocol
     */
    private function verifyWithPublicKey(
        Jwt $jwt,
        RsaSignaturePublicKey $publicKey,
        JwtAlgorithm $algorithm,
    ): DecodedJwtToken {
        return match ($algorithm) {
            JwtAlgorithm::RS256 => new Rs256Protocol($this->clock)->verify($publicKey, $jwt),
            default => throw new InvalidJwtToken(\sprintf('Unsupported algorithm for JWKS verification: %s', $algorithm->value)),
        };
    }

    /**
     * Map JWK algorithm to JWT algorithm enum
     */
    private function mapJwkAlgorithmToJwt(string $jwkAlgorithm): JwtAlgorithm
    {
        return match ($jwkAlgorithm) {
            'RS256' => JwtAlgorithm::RS256,
            default => throw new InvalidJwtToken(\sprintf('Unsupported JWK algorithm: %s', $jwkAlgorithm)),
        };
    }

    // ASN.1 encoding helper methods (same as in JwksKey.php that I deleted)
    private function base64UrlDecode(string $input): string
    {
        $remainder = \strlen($input) % 4;
        if ($remainder) {
            $padLength = 4 - $remainder;
            $input .= \str_repeat('=', $padLength);
        }

        return \base64_decode(\strtr($input, '-_', '+/'));
    }

    private function encodeInteger(string $bytes): string
    {
        if (\ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . $this->encodeLength(\strlen($bytes)) . $bytes;
    }

    private function encodeSequence(string $content): string
    {
        return "\x30" . $this->encodeLength(\strlen($content)) . $content;
    }

    private function encodeObjectIdentifier(string $oid): string
    {
        $parts = \explode('.', $oid);
        $encoded = \chr(40 * \intval($parts[0]) + \intval($parts[1]));
        $counter = \count($parts);

        for ($i = 2; $i < $counter; ++$i) {
            $value = \intval($parts[$i]);
            if ($value < 128) {
                $encoded .= \chr($value);
            } else {
                $bytes = [];
                while ($value > 0) {
                    \array_unshift($bytes, ($value & 0x7F) | 0x80);
                    $value >>= 7;
                }
                $bytes[\count($bytes) - 1] &= 0x7F;
                foreach ($bytes as $byte) {
                    $encoded .= \chr($byte);
                }
            }
        }

        return "\x06" . $this->encodeLength(\strlen($encoded)) . $encoded;
    }

    private function encodeNull(): string
    {
        return "\x05\x00";
    }

    private function encodeBitString(string $bytes): string
    {
        return "\x03" . $this->encodeLength(\strlen($bytes) + 1) . "\x00" . $bytes;
    }

    private function encodeLength(int $length): string
    {
        if ($length < 128) {
            return \chr($length);
        }

        $bytes = [];
        while ($length > 0) {
            \array_unshift($bytes, $length & 0xFF);
            $length >>= 8;
        }

        return \chr(0x80 | \count($bytes)) . \implode('', \array_map('chr', $bytes));
    }
}
