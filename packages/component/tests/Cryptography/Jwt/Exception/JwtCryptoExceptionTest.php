<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicRuntimeException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtCryptoException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtCryptoExceptionTest extends TestCase
{
    #[Test]
    public function extendsCorrectBaseException(): void
    {
        $exception = new JwtCryptoException('Test message');

        self::assertInstanceOf(CryptographicRuntimeException::class, $exception);
        self::assertInstanceOf(JwtException::class, $exception);
        self::assertSame('Test message', $exception->getMessage());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(JwtCryptoException::class);
        $this->expectExceptionMessage('Test JWT crypto exception');

        throw new JwtCryptoException('Test JWT crypto exception');
    }
}
