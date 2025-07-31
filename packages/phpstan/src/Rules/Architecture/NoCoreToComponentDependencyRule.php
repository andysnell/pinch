<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Rules\Architecture;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule to prevent classes from extending a parent class, implementing an interface,
 * or using a trait from a higher-level package (e.g., Component) when they are
 * in the Core package.
 *
 * @implements Rule<InClassNode>
 * @see NoComponentToFrameworkDependencyRule
 */
class NoCoreToComponentDependencyRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class_reflection = $node->getClassReflection();
        $class_name = $class_reflection->getName();

        // Only check classes in the core package
        if (! \str_starts_with($class_name, 'PhoneBurner\\Pinch\\')) {
            return [];
        }

        // Skip if not in core package (no "Component" in namespace)
        if (\str_contains($class_name, '\\Component\\') || \str_contains($class_name, '\\Framework\\')) {
            return [];
        }

        $errors = [];

        // Check parent classes
        $parent_class = $class_reflection->getParentClass();
        if ($parent_class !== null && $this->isComponentClass($parent_class->getName())) {
            $errors[] = RuleErrorBuilder::message(\sprintf(
                'Core package class %s cannot extend component package class %s. This violates the dependency hierarchy (core → component → framework).',
                $class_name,
                $parent_class->getName(),
            ))->identifier('pinch.architecture.coreToComponent')->build();
        }

        // Check implemented interfaces
        foreach ($class_reflection->getInterfaces() as $interface) {
            if ($this->isComponentClass($interface->getName())) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Core package class %s cannot implement component package interface %s. This violates the dependency hierarchy (core → component → framework).',
                    $class_name,
                    $interface->getName(),
                ))->identifier('pinch.architecture.coreToComponent')->build();
            }
        }

        // Check used traits
        foreach ($class_reflection->getTraits() as $trait) {
            if ($this->isComponentClass($trait->getName())) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Core package class %s cannot use component package trait %s. This violates the dependency hierarchy (core → component → framework).',
                    $class_name,
                    $trait->getName(),
                ))->identifier('pinch.architecture.coreToComponent')->build();
            }
        }

        return $errors;
    }

    private function isComponentClass(string $class_name): bool
    {
        return \str_starts_with($class_name, 'PhoneBurner\\Pinch\\Component\\');
    }
}
