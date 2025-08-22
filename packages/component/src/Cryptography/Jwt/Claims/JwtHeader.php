<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;

/**
 * JWT header representation
 */
final readonly class JwtHeader implements \JsonSerializable
{
    public function __construct(
        public JwtAlgorithm $algorithm,
        public string $type = 'JWT',
        public string|null $key_id = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        $data = [
            'alg' => $this->algorithm->value,
            'typ' => $this->type,
        ];

        if ($this->key_id !== null) {
            $data['kid'] = $this->key_id;
        }

        return $data;
    }
}
