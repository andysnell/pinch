<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Jwt;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use Psr\Clock\ClockInterface;

/**
 * Represents a decoded and validated JWT token
 */
final readonly class DecodedJwtToken
{
    public function __construct(
        public JwtHeader $header,
        public JwtPayload $payload,
    ) {
    }

    public static function fromJwt(Jwt $jwt, ClockInterface $clock): self
    {
        $headerJson = $jwt->header();
        $payloadJson = $jwt->payload();

        $headerData = \json_decode($headerJson, true, 2, \JSON_THROW_ON_ERROR);
        $payloadData = \json_decode($payloadJson, true, 2, \JSON_THROW_ON_ERROR);

        // Create header
        $algorithm = JwtAlgorithm::from($headerData['alg']);
        $header = new JwtHeader(
            $algorithm,
            $headerData['typ'] ?? 'JWT',
            $headerData['kid'] ?? null,
        );

        // Create payload
        $payload = new JwtPayload($payloadData, $clock);

        return new self($header, $payload);
    }
}
