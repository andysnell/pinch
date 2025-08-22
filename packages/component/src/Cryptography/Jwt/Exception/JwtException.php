<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Jwt\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicException;

/**
 * Base interface for all JWT-related exceptions
 */
interface JwtException extends CryptographicException
{
}
