<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks;

use PhoneBurner\Pinch\Framework\Cryptography\Exception\InvalidJsonWebKeySet;

/**
 * Represents a JWKS response containing multiple JSON Web Keys
 */
final readonly class JsonWebKeySet
{
    /**
     * @param JsonWebKey[] $keys
     */
    private function __construct(
        public array $keys,
    ) {
    }

    public static function fromJson(string $json): self
    {
        try {
            $data = \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw InvalidJsonWebKeySet::fromInvalidJson($e->getMessage());
        }

        if (! \is_array($data)) {
            throw InvalidJsonWebKeySet::fromInvalidJson('Root must be an object');
        }

        // Check if it's an indexed array (JSON array) vs associative array (JSON object)
        if (\array_is_list($data)) {
            throw InvalidJsonWebKeySet::fromInvalidJson('Root must be an object');
        }

        return self::fromArray($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['keys'])) {
            throw InvalidJsonWebKeySet::fromMissingKeys();
        }

        if (! \is_array($data['keys'])) {
            throw InvalidJsonWebKeySet::fromInvalidKeysStructure();
        }

        if ($data['keys'] === []) {
            throw InvalidJsonWebKeySet::fromEmptyKeySet();
        }

        $keys = [];
        foreach ($data['keys'] as $key_data) {
            if (! \is_array($key_data)) {
                throw InvalidJsonWebKeySet::fromInvalidKeysStructure();
            }

            $keys[] = JsonWebKey::fromArray($key_data);
        }

        return new self($keys);
    }

    /**
     * Find a key by its key ID (kid)
     */
    public function findByKeyId(string $key_id): JsonWebKey|null
    {
        foreach ($this->keys as $key) {
            if ($key->key_id === $key_id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get all signing keys
     *
     * @return JsonWebKey[]
     */
    public function getSigningKeys(): array
    {
        return \array_values(\array_filter($this->keys, static fn(JsonWebKey $key): bool => $key->isSigningKey()));
    }

    /**
     * Get all encryption keys
     *
     * @return JsonWebKey[]
     */
    public function getEncryptionKeys(): array
    {
        return \array_values(\array_filter($this->keys, static fn(JsonWebKey $key): bool => $key->isEncryptionKey()));
    }

    /**
     * Get the number of keys in this set
     */
    public function count(): int
    {
        return \count($this->keys);
    }

    /**
     * Convert back to array format
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'keys' => \array_map(static fn(JsonWebKey $key): array => $key->toArray(), $this->keys),
        ];
    }
}
