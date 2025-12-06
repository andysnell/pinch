<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\App\ErrorHandling;

use PhoneBurner\Pinch\Component\Logging\BufferLogger;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Psr3ExceptionHandler implements ExceptionHandler, LoggerAwareInterface
{
    // phpcs:disable
    public \Closure|ExceptionHandler|null $previous {
        set(ExceptionHandler|callable|null $handler) {
            $this->previous = match (true) {
                $handler instanceof ExceptionHandler, $handler === null => $handler,
                default => $handler(...),
            };
        }
    }
    // phpcs:enable

    /**
     * @param ExceptionHandler|bool $rethrow one of:
     * - true: rethrow the exception
     * - false: do nothing, continue execution
     * - ExceptionHandler: call this wrapped handler with the exception
     */
    public function __construct(
        private LoggerInterface $logger = new BufferLogger(),
        private readonly bool $rethrow = true,
    ) {
    }

    public function __invoke(\Throwable $e): void
    {
        $log_level = $e instanceof \Error ? LogLevel::Critical : LogLevel::Error;
        $message = \sprintf("Uncaught Exception: %s in %s:%s", $e->getMessage(), $e->getFile(), $e->getLine());
        $this->logger->log($log_level->value, $message, [
            'exception' => $e,
        ]);

        if ($this->previous) {
            ($this->previous)($e);
        }

        if ($this->rethrow) {
            throw $e;
        }
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
