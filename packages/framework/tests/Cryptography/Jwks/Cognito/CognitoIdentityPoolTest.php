<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Cryptography\Jwks\Cognito;

use InvalidArgumentException;
use PhoneBurner\Pinch\Framework\Cryptography\Jwks\Cognito\CognitoIdentityPool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CognitoIdentityPoolTest extends TestCase
{
    #[Test]
    public function createsIdentityPoolFromRegionAndPoolId(): void
    {
        $pool = CognitoIdentityPool::create('us-east-1', 'us-east-1_ABC123DEF');

        self::assertSame('us-east-1', $pool->region);
        self::assertSame('us-east-1_ABC123DEF', $pool->user_pool_id);
        self::assertSame('us-east-1_ABC123DEF', $pool->getFullUserPoolId());
        self::assertSame('us-east-1_ABC123DEF', (string)$pool);
    }

    #[Test]
    public function createsIdentityPoolFromUserPoolId(): void
    {
        $pool = CognitoIdentityPool::fromUserPoolId('us-west-2_XYZ789ABC');

        self::assertSame('us-west-2', $pool->region);
        self::assertSame('us-west-2_XYZ789ABC', $pool->user_pool_id);
        self::assertSame('us-west-2_XYZ789ABC', $pool->getFullUserPoolId());
    }

    #[Test]
    public function throwsExceptionForEmptyRegion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AWS region cannot be empty.');

        CognitoIdentityPool::create('', 'us-east-1_ABC123DEF');
    }

    #[Test]
    public function throwsExceptionForEmptyUserPoolId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cognito user pool ID cannot be empty.');

        CognitoIdentityPool::create('us-east-1', '');
    }

    #[Test]
    public function throwsExceptionForMismatchedRegionInPoolId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("User pool ID 'us-west-1_ABC123DEF' must start with region 'us-east-1_'.");

        CognitoIdentityPool::create('us-east-1', 'us-west-1_ABC123DEF');
    }

    #[Test]
    public function throwsExceptionForEmptyUserPoolIdInFromMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User pool ID cannot be empty.');

        CognitoIdentityPool::fromUserPoolId('');
    }

    #[Test]
    #[DataProvider('provideInvalidUserPoolIds')]
    public function throwsExceptionForInvalidUserPoolIdFormat(string $invalid_pool_id): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf("Invalid user pool ID format: '%s'. Expected format: 'region_identifier'.", $invalid_pool_id));

        CognitoIdentityPool::fromUserPoolId($invalid_pool_id);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideInvalidUserPoolIds(): \Iterator
    {
        yield 'no underscore' => ['us-east-1ABC123DEF'];
        yield 'starts with underscore' => ['_ABC123DEF'];
        yield 'ends with underscore' => ['us-east-1_'];
        yield 'multiple underscores but empty parts' => ['us-east-1__'];
        yield 'no region part' => ['_ABC123DEF'];
        yield 'no identifier part' => ['us-east-1_'];
    }
}
