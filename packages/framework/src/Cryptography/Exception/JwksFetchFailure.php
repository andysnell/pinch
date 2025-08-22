<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cryptography\Exception;

use RuntimeException;
use Throwable;

final class JwksFetchFailure extends RuntimeException implements JwksException
{
    public static function fromHttpError(string $uri, int $status_code, string $reason = ''): self
    {
        $message = \sprintf("Failed to fetch JWKS from '%s'. HTTP %d", $uri, $status_code);
        if ($reason !== '') {
            $message .= ': ' . $reason;
        }

        return new self($message);
    }

    public static function fromNetworkError(string $uri, Throwable $previous): self
    {
        return new self(
            \sprintf("Network error while fetching JWKS from '%s': %s", $uri, $previous->getMessage()),
            $previous->getCode(),
            $previous,
        );
    }

    public static function fromTimeout(string $uri): self
    {
        return new self(\sprintf("Timeout while fetching JWKS from '%s'.", $uri));
    }
}
