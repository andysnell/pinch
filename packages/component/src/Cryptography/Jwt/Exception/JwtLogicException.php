<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception\JwtException;

/**
 * Exception thrown for JWT logic errors
 */
class JwtLogicException extends CryptographicLogicException implements JwtException
{
}
