<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * String wrapper around a JWT token.
 *
 * Security Notes:
 * - Rejects tokens with "none" algorithm for security
 * - Validates JWT structure (header.payload.signature)
 * - Uses constant-time base64url decoding
 */
final readonly class Jwt implements \Stringable
{
    public const Encoding ENCODING = Encoding::Base64UrlNoPadding;

    public const string REGEX = '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/';

    private string $header_part;
    private string $payload_part;
    private string $signature_part;

    public function __construct(
        #[\SensitiveParameter]
        public string $value,
    ) {
        if (! \preg_match(self::REGEX, $this->value, $matches)) {
            throw new InvalidJwtToken('Invalid JWT Token');
        }

        $parts = \explode('.', $this->value);
        if (\count($parts) !== 3) {
            throw new InvalidJwtToken('Invalid JWT Token');
        }

        [$this->header_part, $this->payload_part, $this->signature_part] = $parts;

        // Security check: reject "none" algorithm tokens
        $header_json = $this->header();
        $header_data = \json_decode($header_json, true, 2, \JSON_THROW_ON_ERROR);
        if (isset($header_data['alg']) && $header_data['alg'] === 'none') {
            throw new InvalidJwtToken('Invalid JWT Token');
        }
    }

    public function header(): string
    {
        return ConstantTimeEncoder::decode(self::ENCODING, $this->header_part);
    }

    public function payload(): string
    {
        return ConstantTimeEncoder::decode(self::ENCODING, $this->payload_part);
    }

    public function signature(): string
    {
        return $this->signature_part;
    }

    public function token(): self
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
