<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Logging\Monolog\HandlerFactory;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use PhoneBurner\Pinch\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\Pinch\Framework\Logging\Monolog\MonologHandlerFactory;

use const PhoneBurner\Pinch\Framework\APP_ROOT;

/**
 * If this runtime is executing in a tty context, replaces stream loggers that
 * log to stderr or stdout with file-based versions. Otherwise, returns the
 * expected instance.
 */
class ReplaceStdErrStreamMonologHandlerFactory implements MonologHandlerFactory
{
    /**
     * @param class-string<HandlerInterface> $replacement_handler_class
     */
    public function __construct(
        private readonly MonologHandlerFactory $handler_factory,
        private readonly string $replacement_handler_class = RotatingFileHandler::class,
        private readonly string $log_file_path = APP_ROOT . '/storage/logs/pinch-tty.jsonl',
    ) {
    }

    public function make(LoggingHandlerConfigStruct $config): HandlerInterface
    {
        return $this->handler_factory->make(match ($config->handler_options['stream'] ?? '') {
            'php://stderr', 'php://stdout' => new LoggingHandlerConfigStruct(
                handler_class: $this->replacement_handler_class,
                handler_options: [
                    'filename' => $this->log_file_path,
                    'max_files' => 10,
                ],
                formatter_class: JsonFormatter::class,
                level: $config->level,
                bubble: $config->bubble,
            ),
            default => $config,
        });
    }
}
