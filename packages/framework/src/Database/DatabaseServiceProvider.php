<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConnectionProvider as DoctrineConnectionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider as DoctrineEntityManagerProvider;
use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\DeferrableServiceProvider;
use PhoneBurner\Pinch\Component\Cache\Psr6\CacheItemPoolFactory as CacheItemPoolFactoryContract;
use PhoneBurner\Pinch\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\Pinch\Framework\Database\Config\AmpqConfigStruct;
use PhoneBurner\Pinch\Framework\Database\Config\DatabaseConfigStruct;
use PhoneBurner\Pinch\Framework\Database\Config\DoctrineConfigStruct;
use PhoneBurner\Pinch\Framework\Database\Config\RedisConfigStruct;
use PhoneBurner\Pinch\Framework\Database\Doctrine\ConnectionFactory;
use PhoneBurner\Pinch\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\Pinch\Framework\Database\Doctrine\Orm\EntityManagerFactory;
use PhoneBurner\Pinch\Framework\Database\Doctrine\Orm\EntityManagerProvider;
use PhoneBurner\Pinch\Framework\Database\Redis\CachingRedisManager;
use PhoneBurner\Pinch\Framework\Database\Redis\RedisManager;
use Psr\Log\LoggerInterface;

use function PhoneBurner\Pinch\proxy;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class DatabaseServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            AmpqConfigStruct::class,
            CachingRedisManager::class,
            Connection::class,
            ConnectionFactory::class,
            ConnectionProvider::class,
            DoctrineConfigStruct::class,
            DoctrineConnectionProvider::class,
            DoctrineEntityManagerProvider::class,
            EntityManagerFactory::class,
            EntityManagerInterface::class,
            EntityManagerProvider::class,
            RedisConfigStruct::class,
            RedisManager::class,
            \Redis::class,
        ];
    }

    public static function bind(): array
    {
        return [
            RedisManager::class => CachingRedisManager::class,
            DoctrineConnectionProvider::class => ConnectionProvider::class,
            DoctrineEntityManagerProvider::class => EntityManagerProvider::class,
        ];
    }

    /**
     * @param App $app
     * @return void
     * public readonly AmpqConfigStruct|null $ampq = null,
     * public readonly RedisConfigStruct|null $redis = null,
     * public readonly DoctrineConfigStruct|null $doctrine = null,
     */

    #[\Override]
    public static function register(App $app): void
    {
        /** Cannot make a ghost for \Redis because it's an internal class */
        $app->set(
            \Redis::class,
            static fn(App $app): \Redis => $app->get(RedisManager::class)->connect(),
        );

       $app->set(
           AmpqConfigStruct::class,
           static fn(App $app): AmpqConfigStruct => $app->get(DatabaseConfigStruct::class)->ampq ?? new AmpqConfigStruct(),
       );

        $app->set(
            RedisConfigStruct::class,
            static fn(App $app): RedisConfigStruct => $app->get(DatabaseConfigStruct::class)->redis ?? new RedisConfigStruct(),
        );

        $app->set(
            DoctrineConfigStruct::class,
            static fn(App $app): DoctrineConfigStruct => $app->get(DatabaseConfigStruct::class)->doctrine ?? new DoctrineConfigStruct(),
        );

        $app->ghost(CachingRedisManager::class, static fn(CachingRedisManager $ghost): null => $ghost->__construct(
            $app->get(RedisConfigStruct::class),
        ));

        $app->set(ConnectionProvider::class, new ConnectionProvider($app));

        $app->ghost(ConnectionFactory::class, static fn(ConnectionFactory $ghost): null => $ghost->__construct(
            $app->environment,
            $app->get(DoctrineConfigStruct::class),
            $app->get(CacheItemPoolFactoryContract::class),
            $app->get(LoggerInterface::class),
        ));

        $app->set(
            Connection::class,
            proxy(static fn(Connection $proxy): Connection => $app->get(ConnectionFactory::class)->connect()),
        );

        $app->set(EntityManagerProvider::class, new EntityManagerProvider($app));

        $app->ghost(EntityManagerFactory::class, static fn(EntityManagerFactory $ghost): null => $ghost->__construct(
            $app->services,
            $app->environment,
            $app->get(DoctrineConfigStruct::class),
            $app->get(DoctrineConnectionProvider::class),
            $app->get(CacheItemPoolFactory::class),
        ));

        /**
         * The EntityManager is a heavy object, so we'll defer its creation until it's needed.
         * Because we can't create a ghost or proxy for an interface, we'll handle that
         * within the EntityManagerFactory itself.
         */
        $app->set(
            EntityManagerInterface::class,
            static fn(App $app): EntityManagerInterface => $app->get(EntityManagerFactory::class)->ghost(),
        );
    }
}
