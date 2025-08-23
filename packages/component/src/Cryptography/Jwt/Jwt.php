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
 * - Enforces strict input size limits to prevent DoS attacks
 * - Validates JSON structure depth to prevent memory exhaustion
 */
final readonly class Jwt implements \Stringable
{
    public const Encoding ENCODING = Encoding::Base64UrlNoPadding;

    public const string REGEX = '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/';

    // Security limits to prevent DoS attacks
    public const int MAX_TOKEN_LENGTH = 8192;  // Maximum total JWT token length
    public const int MAX_HEADER_LENGTH = 1024;  // Maximum header length
    public const int MAX_PAYLOAD_LENGTH = 4096; // Maximum payload length  
    public const int MAX_JSON_DEPTH = 10;       // Maximum JSON nesting depth

    private string $header_part;
    private string $payload_part;
    private string $signature_part;

    public function __construct(
        #[\SensitiveParameter]
        public string $value,
    ) {
        // Security: Prevent DoS through oversized tokens
        if (\strlen($this->value) > self::MAX_TOKEN_LENGTH) {
            throw new InvalidJwtToken('JWT token exceeds maximum length');
        }

        if (! \preg_match(self::REGEX, $this->value, $matches)) {
            throw new InvalidJwtToken('Invalid JWT Token');
        }

        $parts = \explode('.', $this->value);
        if (\count($parts) !== 3) {
            throw new InvalidJwtToken('Invalid JWT Token');
        }

        [$this->header_part, $this->payload_part, $this->signature_part] = $parts;

        // Security: Validate individual part sizes
        if (\strlen($this->header_part) > self::MAX_HEADER_LENGTH) {
            throw new InvalidJwtToken('JWT header exceeds maximum length');
        }
        if (\strlen($this->payload_part) > self::MAX_PAYLOAD_LENGTH) {
            throw new InvalidJwtToken('JWT payload exceeds maximum length');
        }

        // Security check: reject "none" algorithm tokens
        $header_json = $this->header();
        $header_data = \json_decode($header_json, true, self::MAX_JSON_DEPTH, \JSON_THROW_ON_ERROR);
        if (isset($header_data['alg']) && $header_data['alg'] === 'none') {
            throw new InvalidJwtToken('Invalid JWT Token');
        }

        // Additional security: validate JSON structure
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new InvalidJwtToken('Invalid JWT header JSON structure');
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
