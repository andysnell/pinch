<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\App\ErrorHandling;

/**
 * Default error handler that does nothing other than delegate to the standard
 * PHP error handler (or the outer error handler, if one is wrapping this).
 *
 * By default, if this is the error handler resolved from the container, we do
 * not call `set_error_handler()`. This prevents tests breaking and other weirdness
 * from other error handlers. PHPUnit is very particular about how it handles
 * error handlers.
 */
final readonly class NullErrorHandler implements ErrorHandler
{
    public function __invoke(int $level, string $message, string $file, int $line): false
    {
        return false;
    }
}
