<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute;

/**
 * Marks an "event" object as something primarily intended to be handled by the
 * event sourcing infrastructure and applied to a particular aggregate root.
 *
 * @see Psr14Event for "event" objects intended to be handled by the PSR-14 event
 * dispatcher. Note that a class can be both a PSR-14 and an event source event.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class EventSourceEvent
{
}
