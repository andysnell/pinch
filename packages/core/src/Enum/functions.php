<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Enum;

/**
 * Given a variadic list of enums, return a list of their values.
 *
 * @return array<int|string>
 */
function enum_values(\BackedEnum ...$enum): array
{
    return \array_column($enum, 'value');
}

/**
 * Find attributes on any enum case. Passing a class-string as the
 * $attribute_name will filter the results to only include attributes of
 * that type.
 *
 * @template T of object
 * @param class-string<T>|null $name
 * @return ($name is null ? list<object> : array<T>)
 */
function case_attr_find(
    \UnitEnum $case,
    string|null $name = null,
    bool $use_instanceof = false,
): array {
    static $callback = static fn(\ReflectionAttribute $reflection_attribute): object => $reflection_attribute->newInstance();

    $flags = $use_instanceof ? \ReflectionAttribute::IS_INSTANCEOF : 0;
    return \array_map($callback, new \ReflectionEnumUnitCase($case::class, $case->name)->getAttributes($name, $flags));
}

/**
 * Find the first attribute on a enum case. Passing a class-string as the
 * $attribute_name will filter the results to only include attributes of
 * that type.
 *
 * @template T of object
 * @param class-string<T>|null $name
 * @return ($name is null ? object|null : T|null)
 */
function case_attr_first(
    \UnitEnum $case,
    string|null $name = null,
    bool $use_instanceof = false,
): object|null {
    return case_attr_find($case, $name, $use_instanceof)[0] ?? null;
}

/**
 * Fetch a new instance of the first defined attribute of a given type
 * on an enum case, throwing an exception on failure.
 *
 * @template T of object
 * @param class-string<T> $name
 * @return T&object
 */
function case_attr_fetch(
    \UnitEnum $case,
    string $name,
    bool $use_instanceof = false,
): object {
    return case_attr_find($case, $name, $use_instanceof)[0] ?? throw new \LogicException(
        \sprintf('Attribute %s Not Found for Enum Case %s::%s', $name, $case::class, $case->name),
    );
}
