<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks\Cognito;

use InvalidArgumentException;

/**
 * AWS Cognito Identity Pool identifier
 */
final readonly class CognitoIdentityPool implements \Stringable
{
    private function __construct(
        public string $region,
        public string $user_pool_id,
    ) {
    }

    public static function create(string $region, string $user_pool_id): self
    {
        if ($region === '') {
            throw new InvalidArgumentException('AWS region cannot be empty.');
        }

        if ($user_pool_id === '') {
            throw new InvalidArgumentException('Cognito user pool ID cannot be empty.');
        }

        // Basic validation of user pool ID format (should start with region_identifier)
        if (! \str_starts_with($user_pool_id, $region . '_')) {
            throw new InvalidArgumentException(
                \sprintf("User pool ID '%s' must start with region '%s_'.", $user_pool_id, $region),
            );
        }

        return new self($region, $user_pool_id);
    }

    /**
     * Parse from full user pool ID (e.g., "us-east-1_ABC123DEF")
     */
    public static function fromUserPoolId(string $user_pool_id): self
    {
        if ($user_pool_id === '') {
            throw new InvalidArgumentException('User pool ID cannot be empty.');
        }

        $parts = \explode('_', $user_pool_id, 2);

        if (\count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new InvalidArgumentException(
                \sprintf("Invalid user pool ID format: '%s'. Expected format: 'region_identifier'.", $user_pool_id),
            );
        }

        // Additional validation for empty identifier parts after multiple underscores
        if (\trim($parts[1], '_') === '') {
            throw new InvalidArgumentException(
                \sprintf("Invalid user pool ID format: '%s'. Expected format: 'region_identifier'.", $user_pool_id),
            );
        }

        return new self($parts[0], $user_pool_id);
    }

    public function getFullUserPoolId(): string
    {
        return $this->user_pool_id;
    }

    public function __toString(): string
    {
        return $this->user_pool_id;
    }
}
