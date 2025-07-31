<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\DeferrableServiceProvidersMustProvideAllServicesRule;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\DeferrableServiceProvider;
use PhoneBurner\Pinch\Component\MessageBus\MessageBus;
use PhoneBurner\Pinch\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisManager;
use PhoneBurner\Pinch\Framework\MessageBus\Container\MessageBusContainer;
use PhoneBurner\Pinch\Framework\MessageBus\SymfonyMessageBusAdapter;
use PhoneBurner\Pinch\Framework\MessageBus\SymfonyRoutableMessageBusAdapter;
use PhoneBurner\Pinch\Framework\MessageBus\TransportFactory\AmazonSqsTransportFactory;
use PhoneBurner\Pinch\Framework\MessageBus\TransportFactory\AmqpTransportFactory;
use PhoneBurner\Pinch\Framework\MessageBus\TransportFactory\DoctrineTransportFactory;
use PhoneBurner\Pinch\Framework\MessageBus\TransportFactory\RedisTransportFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

use function PhoneBurner\Pinch\ghost;

class BazServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            MessageBusInterface::class,
            MessageBus::class,
            RoutableMessageBus::class,
            MessageBusContainer::class,
            SymfonyMessageBusAdapter::class,
            SymfonyRoutableMessageBusAdapter::class,
            DoctrineTransportFactory::class,
            AmqpTransportFactory::class,
            AmazonSqsTransportFactory::class,
        ];
    }

    public static function bind(): array
    {
        return [
            MessageBusInterface::class => SymfonyMessageBusAdapter::class,
            MessageBus::class => SymfonyMessageBusAdapter::class,
            RoutableMessageBus::class => SymfonyRoutableMessageBusAdapter::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            MessageBusContainer::class,
            static fn(App $app): MessageBusContainer => new MessageBusContainer(\array_map(
                static fn(array $bus): SymfonyMessageBusAdapter => ghost(
                    static fn(SymfonyMessageBusAdapter $ghost): null => $ghost->__construct(
                        \array_map($app->services->get(...), $bus['middleware'] ?: []),
                    ),
                ),
                $app->config->get('message_bus.bus') ?: [],
            )),
        );

        $app->set(
            SymfonyMessageBusAdapter::class,
            static fn(App $app): SymfonyMessageBusAdapter => $app->get(MessageBusContainer::class)->default(),
        );

        $app->set(
            SymfonyRoutableMessageBusAdapter::class,
            static fn(App $app): SymfonyRoutableMessageBusAdapter => new SymfonyRoutableMessageBusAdapter(
                $app->get(MessageBusContainer::class),
                $app->get(MessageBusContainer::class)->default(),
            ),
        );

        $app->set(
            RedisTransportFactory::class,
            static fn(App $app): RedisTransportFactory => new RedisTransportFactory(
                $app->get(RedisManager::class),
                $app->environment,
            ),
        );

        $app->set(
            DoctrineTransportFactory::class,
            static fn(App $app): DoctrineTransportFactory => new DoctrineTransportFactory(
                $app->get(ConnectionProvider::class),
                new PhpSerializer(),
            ),
        );

        $app->set(
            AmqpTransportFactory::class,
            static fn(App $app): AmqpTransportFactory => new AmqpTransportFactory(),
        );

        $app->set(
            AmazonSqsTransportFactory::class,
            static fn(App $app): AmazonSqsTransportFactory => new AmazonSqsTransportFactory(),
        );
    }
}
