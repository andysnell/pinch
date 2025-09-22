<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\EventSourcing;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\DeferrableServiceProvider;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class EventSourcingServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            ClassNameInflector::class,
            MessageSerializer::class,
        ];
    }

    public static function bind(): array
    {
        return [];
    }

    public static function register(App $app): void
    {
        $app->set(
            ClassNameInflector::class,
            static fn(App $app): ClassNameInflector => new CachingClassNameInflector(
                new DotSeparatedSnakeCaseInflector(),
            ),
        );

        $app->set(
            MessageSerializer::class,
            static fn(App $app): MessageSerializer => new ConstructingMessageSerializer(
                $app->get(ClassNameInflector::class),
            ),
        );
    }
}
