<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute\Usage;

/**
 * Usages of this attribute have code that intentionally deviates from
 * the project standards and conventions. The reason for the deviation
 * MUST be documented in the usage PHP docblock. The targets of this attribute
 * should not be used as templates for implementing new code.
 *
 * @see HotPathOptimization for marking "unconventional" code optimized for performance
 */
#[\Attribute]
final class ConventionDeviation
{
}
