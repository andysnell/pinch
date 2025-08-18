<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute\Usage;

/**
 * Marks a function or method as being a hot execution path (a.k.a. "critical path" or
 * "fast path"). This attribute is intended to be used to signal to tooling and
 * developers that the code should be carefully considered for optimization and
 * should not be changed in a way that could significantly reduce performance.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class HotPath
{
}
