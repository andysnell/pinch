<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidKey;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyId;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

use function PhoneBurner\Pinch\String\bytes;

/**
 * RSA public key for signature verification
 *
 * Supports RSA keys of 2048, 3072, or 4096 bits for JWT RS256 signatures
 */
final readonly class RsaSignaturePublicKey implements PublicKey
{
    public const int MIN_KEY_SIZE = 2048;
    public const int RECOMMENDED_KEY_SIZE = 2048;
    public const array ALLOWED_KEY_SIZES = [2048, 3072, 4096];

    private \OpenSSLAsymmetricKey $key;
    public readonly int $keySize;

    public function __construct(#[\SensitiveParameter] BinaryString|string $pemData)
    {
        $pemData = bytes($pemData);
        $key = \openssl_pkey_get_public($pemData);

        if ($key === false) {
            throw InvalidKey::invalid('Invalid RSA public key PEM data');
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
        $details = \openssl_pkey_get_details($this->key);
        return $details['key'] ?? throw InvalidKey::invalid('Cannot export RSA public key');
    }

    public function id(): KeyId
    {
        return KeyId::ofKey($this);
    }

    public function public(): static
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
        return $this->bytes(); // Public keys can be safely stringified
    }

    public function __serialize(): array
    {
        return ['pem' => $this->bytes()];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct($data['pem']);
    }

    public function jsonSerialize(): string
    {
        return $this->bytes();
    }

    public function export(
        Encoding|null $encoding = null,
        bool $prefix = false,
    ): string {
        $encoding ??= BinaryString::DEFAULT_ENCODING;

        $encoded = ConstantTimeEncoder::encode($encoding, $this->bytes());

        if ($prefix) {
            return $encoding->prefix() . $encoded;
        }

        return $encoded;
    }
}
