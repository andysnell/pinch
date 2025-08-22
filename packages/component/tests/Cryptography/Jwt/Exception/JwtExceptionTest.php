<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtExceptionTest extends TestCase
{
    #[Test]
    public function interfaceExtendsCorrectBaseInterface(): void
    {
        $reflection = new \ReflectionClass(JwtException::class);

        self::assertTrue($reflection->isInterface());
        self::assertTrue($reflection->implementsInterface(CryptographicException::class));
    }
}
