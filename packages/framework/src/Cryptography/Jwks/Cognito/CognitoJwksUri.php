<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Jwks\Cognito;

use PhoneBurner\Pinch\Framework\Cryptography\Jwks\JwksUri;

/**
 * AWS Cognito JWKS URI generator
 */
final readonly class CognitoJwksUri implements \Stringable
{
    private function __construct(
        public CognitoIdentityPool $identity_pool,
        public JwksUri $jwks_uri,
    ) {
    }

    public static function create(CognitoIdentityPool $identity_pool): self
    {
        $uri = self::generateCognitoJwksUrl($identity_pool);
        $jwks_uri = JwksUri::fromString($uri);

        return new self($identity_pool, $jwks_uri);
    }

    /**
     * Create from user pool ID (e.g., "us-east-1_ABC123DEF")
     */
    public static function fromUserPoolId(string $user_pool_id): self
    {
        $identity_pool = CognitoIdentityPool::fromUserPoolId($user_pool_id);
        return self::create($identity_pool);
    }

    /**
     * Create from region and user pool ID components
     */
    public static function fromComponents(string $region, string $user_pool_id): self
    {
        $identity_pool = CognitoIdentityPool::create($region, $user_pool_id);
        return self::create($identity_pool);
    }

    public function getJwksUri(): JwksUri
    {
        return $this->jwks_uri;
    }

    public function toString(): string
    {
        return $this->jwks_uri->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Generate the standard AWS Cognito JWKS URL
     */
    private static function generateCognitoJwksUrl(CognitoIdentityPool $identity_pool): string
    {
        return \sprintf(
            'https://cognito-idp.%s.amazonaws.com/%s/.well-known/jwks.json',
            $identity_pool->region,
            $identity_pool->user_pool_id,
        );
    }
}
