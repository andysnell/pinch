<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Claims;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\ExpiredJwtToken;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\InvalidJwtToken;
use Psr\Clock\ClockInterface;

/**
 * JWT payload claims with validation
 *
 * Security Notes:
 * - Validates exp, iat, and nbf claims when present
 * - Prevents integer overflow attacks in timestamps
 * - Enforces reasonable timestamp ranges to prevent DoS
 * - Validates claim value types to prevent type confusion
 */
final readonly class JwtPayload implements \JsonSerializable
{
    // Security limits for timestamp validation
    public const int MIN_TIMESTAMP = 946684800;    // Year 2000 (prevent negative/ancient dates)
    public const int MAX_TIMESTAMP = 4102444800;   // Year 2100 (prevent far future dates)
    public const int CLOCK_SKEW_TOLERANCE = 300;  // 5 minutes clock skew tolerance

    public function __construct(
        public array $claims,
        private ClockInterface $clock,
    ) {
        // Security: Validate timestamp claims for potential attacks
        $this->validateTimestampClaims();
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

        $timestamp = $this->parseTimestamp($this->claims['exp'], 'exp');
        return $timestamp ? $timestamp->setTimezone(new \DateTimeZone('UTC')) : null;
    }

    public function issuedAt(): \DateTimeImmutable|null
    {
        if (! isset($this->claims['iat'])) {
            return null;
        }

        $timestamp = $this->parseTimestamp($this->claims['iat'], 'iat');
        return $timestamp ? $timestamp->setTimezone(new \DateTimeZone('UTC')) : null;
    }

    public function notBefore(): \DateTimeImmutable|null
    {
        if (! isset($this->claims['nbf'])) {
            return null;
        }

        $timestamp = $this->parseTimestamp($this->claims['nbf'], 'nbf');
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

        // Check expiration (with clock skew tolerance)
        $expiration = $this->expiration();
        if ($expiration !== null) {
            $expirationWithSkew = $expiration->modify('+' . self::CLOCK_SKEW_TOLERANCE . ' seconds');
            if ($now > $expirationWithSkew) {
                throw new ExpiredJwtToken('JWT token has expired');
            }
        }

        // Check not before (with clock skew tolerance)
        $notBefore = $this->notBefore();
        if ($notBefore !== null) {
            $notBeforeWithSkew = $notBefore->modify('-' . self::CLOCK_SKEW_TOLERANCE . ' seconds');
            if ($now < $notBeforeWithSkew) {
                throw new InvalidJwtToken('JWT token is not yet valid');
            }
        }

        // Validate issued at is not in the future (with clock skew tolerance)
        $issuedAt = $this->issuedAt();
        if ($issuedAt !== null) {
            $issuedAtWithSkew = $issuedAt->modify('-' . self::CLOCK_SKEW_TOLERANCE . ' seconds');
            if ($now < $issuedAtWithSkew) {
                throw new InvalidJwtToken('JWT token issued in the future');
            }
        }
    }

    public function jsonSerialize(): array
    {
        return $this->claims;
    }

    /**
     * Validate timestamp claims during construction to prevent attacks
     */
    private function validateTimestampClaims(): void
    {
        $timestampClaims = ['exp', 'iat', 'nbf'];
        
        foreach ($timestampClaims as $claim) {
            if (isset($this->claims[$claim])) {
                $this->parseTimestamp($this->claims[$claim], $claim);
            }
        }
    }

    /**
     * Safely parse and validate timestamp values
     */
    private function parseTimestamp(mixed $value, string $claimName): \DateTimeImmutable|null
    {
        // Security: Validate timestamp value type to prevent type confusion
        if (! \is_numeric($value)) {
            throw new InvalidJwtToken(\sprintf('Invalid %s claim: must be numeric timestamp', $claimName));
        }

        $timestamp = (int) $value;
        
        // Security: Prevent integer overflow and unreasonable timestamp ranges
        if ($timestamp < self::MIN_TIMESTAMP || $timestamp > self::MAX_TIMESTAMP) {
            throw new InvalidJwtToken(\sprintf(
                'Invalid %s claim: timestamp %d outside allowed range (%d - %d)',
                $claimName,
                $timestamp,
                self::MIN_TIMESTAMP,
                self::MAX_TIMESTAMP
            ));
        }

        $dateTime = \DateTimeImmutable::createFromFormat('U', (string)$timestamp);
        if ($dateTime === false) {
            throw new InvalidJwtToken(\sprintf('Invalid %s claim: failed to parse timestamp %d', $claimName, $timestamp));
        }

        return $dateTime;
    }
}
