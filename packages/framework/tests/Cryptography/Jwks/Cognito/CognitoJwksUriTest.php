<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks\Cognito;

use PhoneBurner\Pinch\Framework\Cryptography\Jwks\Cognito\CognitoIdentityPool;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\Cognito\CognitoJwksUri;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CognitoJwksUriTest extends TestCase
{
    #[Test]
    public function createsCognitoJwksUriFromIdentityPool(): void
    {
        $identity_pool = CognitoIdentityPool::create('us-east-1', 'us-east-1_ABC123DEF');
        $cognito_jwks_uri = CognitoJwksUri::create($identity_pool);

        $expected_uri = 'https://cognito-idp.us-east-1.amazonaws.com/us-east-1_ABC123DEF/.well-known/jwks.json';

        self::assertSame($identity_pool, $cognito_jwks_uri->identity_pool);
        self::assertSame($expected_uri, $cognito_jwks_uri->getJwksUri()->toString());
        self::assertSame($expected_uri, $cognito_jwks_uri->toString());
        self::assertSame($expected_uri, (string)$cognito_jwks_uri);
    }

    #[Test]
    public function createsCognitoJwksUriFromUserPoolId(): void
    {
        $cognito_jwks_uri = CognitoJwksUri::fromUserPoolId('us-west-2_XYZ789ABC');

        $expected_uri = 'https://cognito-idp.us-west-2.amazonaws.com/us-west-2_XYZ789ABC/.well-known/jwks.json';

        self::assertSame('us-west-2', $cognito_jwks_uri->identity_pool->region);
        self::assertSame('us-west-2_XYZ789ABC', $cognito_jwks_uri->identity_pool->user_pool_id);
        self::assertSame($expected_uri, $cognito_jwks_uri->getJwksUri()->toString());
    }

    #[Test]
    public function createsCognitoJwksUriFromComponents(): void
    {
        $cognito_jwks_uri = CognitoJwksUri::fromComponents('eu-west-1', 'eu-west-1_DEF456GHI');

        $expected_uri = 'https://cognito-idp.eu-west-1.amazonaws.com/eu-west-1_DEF456GHI/.well-known/jwks.json';

        self::assertSame('eu-west-1', $cognito_jwks_uri->identity_pool->region);
        self::assertSame('eu-west-1_DEF456GHI', $cognito_jwks_uri->identity_pool->user_pool_id);
        self::assertSame($expected_uri, $cognito_jwks_uri->getJwksUri()->toString());
    }

    #[Test]
    public function generatesCorrectUriForDifferentRegions(): void
    {
        $regions_and_pools = [
            ['us-east-1', 'us-east-1_ABC123'],
            ['us-west-2', 'us-west-2_DEF456'],
            ['eu-west-1', 'eu-west-1_GHI789'],
            ['ap-southeast-1', 'ap-southeast-1_JKL012'],
        ];

        foreach ($regions_and_pools as [$region, $pool_id]) {
            $cognito_jwks_uri = CognitoJwksUri::fromComponents($region, $pool_id);
            $expected_uri = \sprintf('https://cognito-idp.%s.amazonaws.com/%s/.well-known/jwks.json', $region, $pool_id);

            self::assertSame($expected_uri, $cognito_jwks_uri->toString());
        }
    }
}
