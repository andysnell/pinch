<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute\Usage;

/**
 * Marks a function or method as having been optimized for a hot execution path.
 * The code may deviate significantly from project conventions. Usage of this
 * attribute indicates that the code, which may look like it should be refactored,
 * should not be changed without careful consideration. It also indicates that the
 * function or method should not be used as a template for implementing new code.
 *
 * Code that uses this attribute should have a clear and well-documented explanation
 * of why and how the code is optimized in the corresponding docblock.
 *
 * @see HotPath for marking performance-critical code. Most targets using this attribute should also use HotPath.
 * @see ConventionDeviation for marking code that deviates from project standards and conventions.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class HotPathOptimization
{
}
