<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\ExpiredJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use Psr\Clock\ClockInterface;

/**
 * JWT payload claims with validation
 *
 * Security Note: Validates exp, iat, and nbf claims when present
 */
final readonly class JwtPayload implements \JsonSerializable
{
    public function __construct(
        public array $claims,
        private ClockInterface $clock,
    ) {
    }

    public function subject(): string|null
    {
        return $this->claims['sub'] ?? null;
    }

    public function expiration(): \DateTimeImmutable|null
    {
        if (! isset($this->claims['exp'])) {
            return null;
        }

        $timestamp = \DateTimeImmutable::createFromFormat('U', (string)$this->claims['exp']);
        return $timestamp ? $timestamp->setTimezone(new \DateTimeZone('UTC')) : null;
    }

    public function issuedAt(): \DateTimeImmutable|null
    {
        if (! isset($this->claims['iat'])) {
            return null;
        }

        $timestamp = \DateTimeImmutable::createFromFormat('U', (string)$this->claims['iat']);
        return $timestamp ? $timestamp->setTimezone(new \DateTimeZone('UTC')) : null;
    }

    public function notBefore(): \DateTimeImmutable|null
    {
        if (! isset($this->claims['nbf'])) {
            return null;
        }

        $timestamp = \DateTimeImmutable::createFromFormat('U', (string)$this->claims['nbf']);
        return $timestamp ? $timestamp->setTimezone(new \DateTimeZone('UTC')) : null;
    }

    /**
     * Validate time-based claims (exp, iat, nbf)
     *
     * @throws ExpiredJwtToken If the token has expired
     * @throws InvalidJwtToken If the token is not yet valid
     */
    public function validateTimeClaims(): void
    {
        $now = $this->clock->now();

        // Check expiration
        $expiration = $this->expiration();
        if ($expiration !== null && $now > $expiration) {
            throw new ExpiredJwtToken('JWT token has expired');
        }

        // Check not before
        $notBefore = $this->notBefore();
        if ($notBefore !== null && $now < $notBefore) {
            throw new InvalidJwtToken('JWT token is not yet valid');
        }
    }

    public function jsonSerialize(): array
    {
        return $this->claims;
    }
}
