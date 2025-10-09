<?php

declare(strict_types=1);

namespace App;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\Pinch\Component\App\ServiceProvider;
use PhoneBurner\Pinch\Component\Cache\Lock\LockFactory;
use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Framework\App\Config\AppConfigStruct;
use PhoneBurner\Pinch\Framework\Logging\Monolog\HandlerFactory\ContainerHandlerFactory;
use PhoneBurner\Pinch\Framework\Logging\Monolog\HandlerFactory\ReplaceStdErrStreamMonologHandlerFactory;
use PhoneBurner\Pinch\Framework\Logging\Monolog\MonologHandlerFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

/**
 * @codeCoverageIgnore
 */
class ApplicationServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [AppConfigStruct::class => ApplicationConfigStruct::class];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            ApplicationConfigStruct::class,
            static fn(App $app): ApplicationConfigStruct => $app->config->get('app'),
        );

        $app->set(ApplicationRouteProvider::class, NewInstanceServiceFactory::singleton());

        $app->set(
            ApplicationScheduleProvider::class,
            static fn(App $app): ApplicationScheduleProvider => new ApplicationScheduleProvider(
                $app->get(CacheItemPoolInterface::class),
                $app->get(LockFactory::class),
                $app->get(SymfonyEventDispatcherInterface::class),
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            MonologHandlerFactory::class,
            static function (App $app): MonologHandlerFactory {
                $handler_factory = $app->get(ContainerHandlerFactory::class);
                if ($app->environment->context !== Context::Http && \posix_isatty(\STDERR)) {
                    return new ReplaceStdErrStreamMonologHandlerFactory(
                        $handler_factory,
                    );
                }

                return $handler_factory;
            },
        );
    }
}
