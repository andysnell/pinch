<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyId;
use PhoneBurner\Pinch\Exception\SerializationProhibited;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * RSA key pair for JWT RS256 signatures
 *
 * Supports RSA keys of 2048, 3072, or 4096 bits
 */
final readonly class RsaSignatureKeyPair implements KeyPair
{
    public const int DEFAULT_KEY_SIZE = 3072;
    public const array ALLOWED_KEY_SIZES = [3072, 4096];

    public RsaSignatureSecretKey $secret;
    public RsaSignaturePublicKey $public;

    public function __construct(#[\SensitiveParameter] BinaryString|string $privateKeyPem)
    {
        $this->secret = new RsaSignatureSecretKey($privateKeyPem);
        $this->public = $this->secret->publicKey();

        // Verify both keys have the same size
        if ($this->secret->keySize !== $this->public->keySize) {
            throw InvalidKeyPair::invalid('RSA secret and public key sizes do not match');
        }
    }

    /**
     * Generate a new RSA key pair with specified bit length
     */
    public static function generate(int $keySize = self::DEFAULT_KEY_SIZE): static
    {
        if (! \in_array($keySize, self::ALLOWED_KEY_SIZES)) {
            throw InvalidKeyPair::invalid(
                \sprintf(
                    'RSA key size %d is not supported. Allowed sizes: %s',
                    $keySize,
                    \implode(', ', self::ALLOWED_KEY_SIZES),
                ),
            );
        }

        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => $keySize,
            'private_key_type' => \OPENSSL_KEYTYPE_RSA,
        ];

        $resource = \openssl_pkey_new($config);
        if ($resource === false) {
            throw InvalidKeyPair::invalid('Failed to generate RSA key pair');
        }

        if (! \openssl_pkey_export($resource, $privateKeyPem)) {
            throw InvalidKeyPair::invalid('Failed to export RSA private key');
        }

        return new self($privateKeyPem);
    }

    /**
     * Create key pair from PEM-encoded private key
     */
    public static function fromPrivateKeyPem(#[\SensitiveParameter] string $privateKeyPem): self
    {
        return new self($privateKeyPem);
    }

    public function secret(): RsaSignatureSecretKey
    {
        return $this->secret;
    }

    public function public(): RsaSignaturePublicKey
    {
        return $this->public;
    }

    public function publicKey(): RsaSignaturePublicKey
    {
        return $this->public;
    }

    public function bytes(): string
    {
        // SECURITY: Private key material MUST NOT be exposed
        // This would expose the secret key through delegation
        throw new SerializationProhibited('Key pair bytes cannot be exported - contains private key material');
    }

    public function id(): KeyId
    {
        return KeyId::ofKey($this->public);
    }

    public function length(): int
    {
        // RSA key length varies, return the secret key's bit size / 8
        $result = (int)($this->secret->keySize / 8);
        \assert($result >= 0);
        return $result;
    }

    public function __toString(): string
    {
        throw new SerializationProhibited();
    }

    public function __serialize(): array
    {
        throw new SerializationProhibited();
    }

    public function __unserialize(array $data): void
    {
        throw new SerializationProhibited();
    }

    public function jsonSerialize(): string
    {
        throw new SerializationProhibited();
    }

    public static function import(
        #[\SensitiveParameter] string $string,
        Encoding|null $encoding = null,
    ): static {
        return new self($string);
    }

    public static function tryImport(
        #[\SensitiveParameter] string|null $string,
        Encoding|null $encoding = null,
    ): static|null {
        if ($string === null) {
            return null;
        }
        try {
            return self::import($string, $encoding);
        } catch (\Throwable) {
            return null;
        }
    }

    public function export(
        Encoding|null $encoding = null,
        bool $prefix = false,
    ): string {
        // SECURITY: Export would expose private key material through bytes() method
        // Key pairs containing secret keys must not be exportable
        throw new SerializationProhibited('Key pair export prohibited - contains private key material');
    }
}
