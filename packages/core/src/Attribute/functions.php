<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Attribute;

use function PhoneBurner\Pinch\Type\is_class_string;

/**
 * Find attributes on any object, class-string, or reflection instance that
 * supports the "getAttributes" method. Passing a class-string as the
 * $attribute_name will filter the results to only include attributes of
 * that type.
 *
 * @template T of object
 * @param \Reflector|object|class-string $class_or_reflection
 * @param class-string<T>|null $attribute_name
 * @return ($attribute_name is null ? list<object> : array<T>)
 */
function attr_find(
    object|string $class_or_reflection,
    string|null $attribute_name = null,
    bool $use_instanceof = false,
): array {
    return \array_map(
        static fn(\ReflectionAttribute $reflection_attribute): object => $reflection_attribute->newInstance(),
        attr_find_reflections($class_or_reflection, $attribute_name, $use_instanceof),
    );
}

/**
 * Find the first attribute on any object, class-string, or reflection instance that
 * supports the "getAttributes" method. Passing a class-string as the
 * $attribute_name will filter the results to only include attributes of
 * that type.
 *
 * @template T of object
 * @param \Reflector|object|class-string $class_or_reflection
 * @param class-string<T>|null $attribute_name
 * @return ($attribute_name is null ? object : T)|null
 */
function attr_first(
    object|string $class_or_reflection,
    string|null $attribute_name = null,
    bool $use_instanceof = false,
): object|null {
    return attr_find($class_or_reflection, $attribute_name, $use_instanceof)[0] ?? null;
}

/**
 * Find the first attribute on any object, class-string, or reflection instance that
 * supports the "getAttributes" method. Passing a class-string as the
 * $attribute_name will filter the results to only include attributes of
 * that type.
 *
 * @template T of object
 * @param \Reflector|object|class-string $class_or_reflection
 * @param class-string<T> $attribute_name
 * @return T
 */
function attr_fetch(
    object|string $class_or_reflection,
    string $attribute_name,
    bool $use_instanceof = false,
): object {
    return attr_find($class_or_reflection, $attribute_name, $use_instanceof)[0] ?? throw new \LogicException(\sprintf(
        'Attribute %s Not Found for %s',
        $attribute_name,
        \is_object($class_or_reflection) ? $class_or_reflection::class : $class_or_reflection,
    ));
}

/**
 * @template T of object
 * @param \Reflector|object|class-string $class_or_reflection
 * @param class-string<T>|null $attribute_name
 * @return ($attribute_name is null ? array<\ReflectionAttribute<object>> : array<\ReflectionAttribute<T>>)
 * @todo PHP 8.5: Add support for \ReflectionConstant
 */
function attr_find_reflections(
    object|string $class_or_reflection,
    string|null $attribute_name = null,
    bool $use_instanceof = false,
): array {
    return (match (true) {
        is_class_string($class_or_reflection) => new \ReflectionClass($class_or_reflection),
        ! $class_or_reflection instanceof \Reflector => new \ReflectionClass($class_or_reflection),
        $class_or_reflection instanceof \ReflectionClass, // Covers \ReflectionObject and \ReflectionEnum
            $class_or_reflection instanceof \ReflectionClassConstant, // Covers \ReflectionEnumBackedCase and \ReflectionEnumUnitCase
            $class_or_reflection instanceof \ReflectionFunctionAbstract, // Covers \ReflectionFunction and \ReflectionMethod
            $class_or_reflection instanceof \ReflectionParameter,
            $class_or_reflection instanceof \ReflectionProperty => $class_or_reflection,
        default => throw new \UnexpectedValueException('Invalid class or reflection'),
    })->getAttributes($attribute_name, $use_instanceof ? \ReflectionAttribute::IS_INSTANCEOF : 0);
}
