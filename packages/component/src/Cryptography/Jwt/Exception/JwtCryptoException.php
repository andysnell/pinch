<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicRuntimeException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtException;

/**
 * Exception thrown for JWT cryptographic errors
 */
class JwtCryptoException extends CryptographicRuntimeException implements JwtException
{
}
