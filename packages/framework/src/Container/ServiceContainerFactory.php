<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Container;

use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\App\App as AppContract;
use PhoneBurner\Pinch\Component\App\DeferrableServiceProvider;
use PhoneBurner\Pinch\Component\App\ServiceContainer;
use PhoneBurner\Pinch\Component\App\ServiceContainer\ServiceContainerAdapter;
use PhoneBurner\Pinch\Component\App\ServiceContainerFactory as ServiceContainerFactoryContract;
use PhoneBurner\Pinch\Component\App\ServiceProvider;
use PhoneBurner\Pinch\Framework\App\AppServiceProvider;
use PhoneBurner\Pinch\Framework\Cache\CacheServiceProvider;
use PhoneBurner\Pinch\Framework\Console\ConsoleServiceProvider;
use PhoneBurner\Pinch\Framework\Database\DatabaseServiceProvider;
use PhoneBurner\Pinch\Framework\EventDispatcher\EventDispatcherServiceProvider;
use PhoneBurner\Pinch\Framework\EventSourcing\EventSourcingServiceProvider;
use PhoneBurner\Pinch\Framework\HealthCheck\HealthCheckServiceProvider;
use PhoneBurner\Pinch\Framework\Http\HttpServiceProvider;
use PhoneBurner\Pinch\Framework\HttpClient\HttpClientServiceProvider;
use PhoneBurner\Pinch\Framework\Logging\LoggingServiceProvider;
use PhoneBurner\Pinch\Framework\Mailer\MailerServiceProvider;
use PhoneBurner\Pinch\Framework\MessageBus\MessageBusServiceProvider;
use PhoneBurner\Pinch\Framework\Notifier\NotifierServiceProvider;
use PhoneBurner\Pinch\Framework\Scheduler\SchedulerServiceProvider;
use PhoneBurner\Pinch\Framework\Storage\StorageServiceProvider;

#[Internal]
class ServiceContainerFactory implements ServiceContainerFactoryContract
{
    /**
     * @var array<class-string<ServiceProvider>>
     */
    public const array FRAMEWORK_PROVIDERS = [
        AppServiceProvider::class,
        CacheServiceProvider::class,
        ConsoleServiceProvider::class,
        DatabaseServiceProvider::class,
        EventDispatcherServiceProvider::class,
        EventSourcingServiceProvider::class,
        HealthCheckServiceProvider::class,
        HttpServiceProvider::class,
        HttpClientServiceProvider::class,
        LoggingServiceProvider::class,
        MailerServiceProvider::class,
        MessageBusServiceProvider::class,
        NotifierServiceProvider::class,
        SchedulerServiceProvider::class,
        StorageServiceProvider::class,
    ];

    /**
     * Initialize the service container and register the service providers in the
     * order they are defined in the framework and application. (Handling of deferrable
     * service providers has been moved into the ServiceContainerAdapter.)
     */
    public function make(#[\SensitiveParameter] AppContract $app): ServiceContainer
    {
        $service_container = new ServiceContainerAdapter($app);
        foreach ([...self::FRAMEWORK_PROVIDERS, ...$app->config->get('container.service_providers') ?: []] as $provider) {
            $service_container->register($provider);
        }
        return $service_container;
    }
}
