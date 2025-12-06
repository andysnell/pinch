<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\App\ErrorHandling;

use PhoneBurner\Pinch\Component\Logging\BufferLogger;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Note that some error levels cannot be handled by a user-defined error
 * handler, since they occur during PHP's initialization or are triggered
 * when the engine is in an unrecoverable state. As of PHP 8.4, these are:
 * - E_CORE_ERROR
 * - E_CORE_WARNING
 * - E_COMPILE_ERROR
 * - E_COMPILE_WARNING
 * - E_PARSE
 * - E_ERROR
 *
 * Additionally, the \E_RECOVERABLE_ERROR level is effectively unused as the only
 * place it can be triggered is when an error occurs when casting an internal class
 * object to a bool.
 */
class Psr3ErrorHandler implements ErrorHandler, LoggerAwareInterface
{
    private const array LEVEL_MAP = [
        \E_USER_ERROR => ['E_USER_ERROR', LogLevel::Error],
        \E_WARNING => ['E_WARNING', LogLevel::Warning],
        \E_USER_WARNING => ['E_USER_WARNING', LogLevel::Warning],
        \E_NOTICE => ['E_NOTICE', LogLevel::Notice],
        \E_USER_NOTICE => ['E_USER_NOTICE', LogLevel::Notice],
        \E_DEPRECATED => ['E_DEPRECATED', LogLevel::Debug],
        \E_USER_DEPRECATED => ['E_USER_DEPRECATED', LogLevel::Debug],
    ];

    // phpcs:disable
    public \Closure|ErrorHandler|null $previous {
        set(ErrorHandler|callable|null $handler) {
            $this->previous = match (true) {
                $handler instanceof ErrorHandler, $handler === null => $handler,
                default => $handler(...),
            };
        }
    }
    // phpcs:enable

    /**
     * @param ErrorHandler|bool $return one of the following:
     *  - true: suppress the default error handler and continue execution
     *  - false: the default error handler is used will continue.
     *  - ErrorHandler: call and return the wrapped error handler's return value
     */
    public function __construct(
        private LoggerInterface $logger = new BufferLogger(),
        public readonly bool $bypass_standard_handler = false,
    ) {
    }

    public function __invoke(int $level, string $message, string $file, int $line): bool
    {
        [$name, $log_level] = self::LEVEL_MAP[$level] ?? ['UNKNOWN ERROR', LogLevel::Critical];
        $this->logger->log($log_level->value, \sprintf('Unhandled %s: %s in %s:%s', $name, $message, $file, $line));
        return $this->previous ? ($this->previous)($level, $message, $file, $line) : $this->bypass_standard_handler;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        // Write any buffered log entries to the new logger
        if ($this->logger instanceof BufferLogger) {
            $this->logger->copy($logger);
        }

        $this->logger = $logger;
    }
}
