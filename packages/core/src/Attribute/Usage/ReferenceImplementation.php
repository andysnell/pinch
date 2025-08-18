<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute\Usage;

/**
 * Marks the target as a reference implementation for a particular interface or
 * use case. This attribute is intended to be used to signal to tooling and
 * developers that the code should be used as the template for implementing new
 * code. It should not be used as a replacement for proper documentation. Targets
 * MUST follow the project standards and conventions.
 */
#[\Attribute]
final class ReferenceImplementation
{
}
