<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKey;

/**
 * Individual JSON Web Key from a JWKS
 */
final readonly class JsonWebKey
{
    /**
     * @param array<string, mixed> $key_data
     */
    private function __construct(
        public string $key_id,
        public string $key_type,
        public string $use,
        public string $algorithm,
        public array $key_data,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['kid'])) {
            throw InvalidJsonWebKey::fromMissingKeyId();
        }

        if (! isset($data['kty'])) {
            throw InvalidJsonWebKey::fromMissingKeyType();
        }

        if (! isset($data['use'])) {
            throw InvalidJsonWebKey::fromMissingUse();
        }

        if (! isset($data['alg'])) {
            throw InvalidJsonWebKey::fromMissingAlgorithm();
        }

        $use = (string)$data['use'];
        if (! \in_array($use, ['sig', 'enc'], true)) {
            throw InvalidJsonWebKey::fromInvalidUse($use);
        }

        // Validate required properties based on key type
        $key_type = (string)$data['kty'];
        switch ($key_type) {
            case 'RSA':
                if (! isset($data['n']) || ! isset($data['e'])) {
                    throw InvalidJsonWebKey::fromInvalidKeyData('n and e required for RSA keys');
                }
                break;
            case 'EC':
                if (! isset($data['crv']) || ! isset($data['x']) || ! isset($data['y'])) {
                    throw InvalidJsonWebKey::fromInvalidKeyData('crv, x, and y required for EC keys');
                }
                break;
        }

        return new self(
            key_id: (string)$data['kid'],
            key_type: $key_type,
            use: $use,
            algorithm: (string)$data['alg'],
            key_data: $data,
        );
    }

    /**
     * Check if this key can be used for signature verification
     */
    public function isSigningKey(): bool
    {
        return $this->use === 'sig';
    }

    /**
     * Check if this key can be used for encryption
     */
    public function isEncryptionKey(): bool
    {
        return $this->use === 'enc';
    }

    /**
     * Get the full key data as array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->key_data;
    }
}
