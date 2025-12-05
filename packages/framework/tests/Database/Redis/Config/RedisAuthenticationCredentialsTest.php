<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Tests\Database\Redis\Config;

use PhoneBurner\Pinch\Framework\Database\Redis\Config\RedisAuthenticationCredentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RedisAuthenticationCredentialsTest extends TestCase
{
    #[Test]
    public function it_creates_credentials_with_user_and_password(): void
    {
        $credentials = new RedisAuthenticationCredentials(
            user: 'admin',
            pass: 'secret123',
        );

        self::assertSame('admin', $credentials->user);
        self::assertSame('secret123', $credentials->pass);
    }

    #[Test]
    public function it_creates_credentials_with_null_user_and_password(): void
    {
        $credentials = new RedisAuthenticationCredentials(
            user: null,
            pass: 'secret123',
        );

        self::assertNull($credentials->user);
        self::assertSame('secret123', $credentials->pass);
    }

    #[Test]
    public function it_throws_exception_when_user_is_empty_string(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Redis authentication username must be null or non-empty-string');

        new RedisAuthenticationCredentials(
            user: '',
            pass: 'secret123',
        );
    }

    #[Test]
    public function it_throws_exception_when_password_is_empty_string(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Redis authentication password must be non-empty-string');

        new RedisAuthenticationCredentials(
            user: 'admin',
            pass: '',
        );
    }

    #[Test]
    public function it_returns_associative_array_with_user_and_password(): void
    {
        $credentials = new RedisAuthenticationCredentials(
            user: 'admin',
            pass: 'secret123',
        );

        $result = $credentials->toArray();

        self::assertSame(['user' => 'admin', 'pass' => 'secret123'], $result);
    }

    #[Test]
    public function it_returns_associative_array_with_null_user_and_password(): void
    {
        $credentials = new RedisAuthenticationCredentials(
            user: null,
            pass: 'secret123',
        );

        $result = $credentials->toArray();

        self::assertSame(['user' => null, 'pass' => 'secret123'], $result);
    }

    #[Test]
    public function it_returns_indexed_array_with_user_and_password(): void
    {
        $credentials = new RedisAuthenticationCredentials(
            user: 'admin',
            pass: 'secret123',
        );

        $result = $credentials->toArray(assoc: false);

        self::assertSame(['admin', 'secret123'], $result);
    }

    #[Test]
    public function it_returns_indexed_array_with_null_user_and_password(): void
    {
        $credentials = new RedisAuthenticationCredentials(
            user: null,
            pass: 'secret123',
        );

        $result = $credentials->toArray(assoc: false);

        self::assertSame([null, 'secret123'], $result);
    }

    #[Test]
    public function it_serializes_and_unserializes_credentials_with_user(): void
    {
        $original = new RedisAuthenticationCredentials(
            user: 'admin',
            pass: 'secret123',
        );

        $serialized = \serialize($original);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(RedisAuthenticationCredentials::class, $unserialized);
        self::assertSame('admin', $unserialized->user);
        self::assertSame('secret123', $unserialized->pass);
    }

    #[Test]
    public function it_serializes_and_unserializes_credentials_without_user(): void
    {
        $original = new RedisAuthenticationCredentials(
            user: null,
            pass: 'secret123',
        );

        $serialized = \serialize($original);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(RedisAuthenticationCredentials::class, $unserialized);
        self::assertNull($unserialized->user);
        self::assertSame('secret123', $unserialized->pass);
    }
}
