<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidKey;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyId;
use PhoneBurner\Pinch\Exception\SerializationProhibited;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\Encoding\Encoding;

use function PhoneBurner\Pinch\String\bytes;

/**
 * RSA secret key for signature creation
 *
 * Supports RSA keys of 2048, 3072, or 4096 bits for JWT RS256 signatures
 */
final readonly class RsaSignatureSecretKey implements SecretKey
{
    public const int MIN_KEY_SIZE = 3072;
    public const int RECOMMENDED_KEY_SIZE = 3072;
    public const array ALLOWED_KEY_SIZES = [3072, 4096];

    private \OpenSSLAsymmetricKey $key;
    public readonly int $keySize;

    public function __construct(#[\SensitiveParameter] BinaryString|string $pemData)
    {
        $pemData = bytes($pemData);
        $key = \openssl_pkey_get_private($pemData);

        if ($key === false) {
            throw InvalidKey::invalid('Invalid RSA private key PEM data');
        }

        $details = \openssl_pkey_get_details($key);
        if ($details === false || $details['type'] !== \OPENSSL_KEYTYPE_RSA) {
            throw InvalidKey::invalid('Key is not an RSA key');
        }

        $keySize = $details['bits'];
        if (! \in_array($keySize, self::ALLOWED_KEY_SIZES)) {
            throw InvalidKey::invalid(
                \sprintf(
                    'RSA key size %d is not supported. Allowed sizes: %s',
                    $keySize,
                    \implode(', ', self::ALLOWED_KEY_SIZES),
                ),
            );
        }

        $this->key = $key;
        $this->keySize = $keySize;
    }

    public static function fromPem(string $pemData): self
    {
        return new self($pemData);
    }

    public function openSslKey(): \OpenSSLAsymmetricKey
    {
        return $this->key;
    }

    public function bytes(): string
    {
        // SECURITY: Private key material MUST NOT be exposed
        // This method would allow extraction of sensitive key data
        throw new SerializationProhibited('Private key bytes cannot be exported for security reasons');
    }

    public function id(): KeyId
    {
        return KeyId::ofKey($this->publicKey());
    }

    public function publicKey(): RsaSignaturePublicKey
    {
        $details = \openssl_pkey_get_details($this->key);
        if ($details === false) {
            throw InvalidKey::invalid('Failed to get key details');
        }
        return new RsaSignaturePublicKey($details['key']);
    }

    public function secret(): static
    {
        return $this;
    }

    public function length(): int
    {
        $result = (int)($this->keySize / 8);
        \assert($result >= 0);
        return $result;
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

    public function export(
        Encoding|null $encoding = null,
        bool $prefix = false,
    ): string {
        throw new SerializationProhibited();
    }
}
