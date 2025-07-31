<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute\Usage;

/**
 * Marks a class as having a stable fully qualified name (FQCN) that cannot be
 * changed without breaking compatibility with external systems or serialized data.
 * Use this attribute to signal to tooling and developers that the class name
 * must remain stable and should not be changed lightly.
 *
 * This attribute should be applied to classes whose FQCNs are persisted in external systems,
 * such as serialized payloads, event streams, or database records. Renaming these classes —
 * including changing their namespace — may break deserialization or other reflective access,
 * so such changes should be considered breaking. This may include moving the class
 * to a different namespace even if the class name itself remains the same.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class StableClassName
{
    public function __construct(
        /**
         * Whether the namespace is part of the externally stable identifier.
         * Set to false if only the short class name is referenced externally,
         * and namespace-only changes are safe.
         */
        public bool $including_namespace = true,
        /**
         * Whether the class name is referenced outside the codebase (e.g., in
         * serialized data persisted in a database). If false, the attribute is
         * purely advisory, indicating that other non-evident/automatable code
         * changes are required when renaming the class. For example, if the
         * class is included by filepath in a configuration file or test.
         */
        public bool $externally_referenced = true,
        /**
         * A human-readable detail about the class that is stable and can be used
         * to identify where the class is referenced or how it is used externally.
         */
        public string $detail = '',
    ) {
    }
}
