<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtLogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtLogicExceptionTest extends TestCase
{
    #[Test]
    public function extendsCorrectBaseException(): void
    {
        $exception = new JwtLogicException('Test message');

        self::assertInstanceOf(CryptographicLogicException::class, $exception);
        self::assertInstanceOf(JwtException::class, $exception);
        self::assertSame('Test message', $exception->getMessage());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(JwtLogicException::class);
        $this->expectExceptionMessage('Test JWT logic exception');

        throw new JwtLogicException('Test JWT logic exception');
    }
}
