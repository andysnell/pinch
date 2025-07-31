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
 * or using a trait from a higher-level package (e.g., Framework) when they are
 * in the Component package.
 *
 * @implements Rule<InClassNode>
 * @see NoCoreToComponentDependencyRule
 */
class NoComponentToFrameworkDependencyRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class_reflection = $node->getClassReflection();
        $class_name = $class_reflection->getName();

        // Only check classes in the component package
        if (! \str_starts_with($class_name, 'PhoneBurner\\Pinch\\Component\\')) {
            return [];
        }

        $errors = [];

        // Check parent classes
        $parent_class = $class_reflection->getParentClass();
        if ($parent_class !== null && $this->isFrameworkClass($parent_class->getName())) {
            $errors[] = RuleErrorBuilder::message(\sprintf(
                'Component package class %s cannot extend framework package class %s. This violates the dependency hierarchy (core → component → framework).',
                $class_name,
                $parent_class->getName(),
            ))->identifier('pinch.architecture.componentToFramework')->build();
        }

        // Check implemented interfaces
        foreach ($class_reflection->getInterfaces() as $interface) {
            if ($this->isFrameworkClass($interface->getName())) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Component package class %s cannot implement framework package interface %s. This violates the dependency hierarchy (core → component → framework).',
                    $class_name,
                    $interface->getName(),
                ))->identifier('pinch.architecture.componentToFramework')->build();
            }
        }

        // Check used traits
        foreach ($class_reflection->getTraits() as $trait) {
            if ($this->isFrameworkClass($trait->getName())) {
                $errors[] = RuleErrorBuilder::message(\sprintf(
                    'Component package class %s cannot use framework package trait %s. This violates the dependency hierarchy (core → component → framework).',
                    $class_name,
                    $trait->getName(),
                ))->identifier('pinch.architecture.componentToFramework')->build();
            }
        }

        return $errors;
    }

    private function isFrameworkClass(string $class_name): bool
    {
        return \str_starts_with($class_name, 'PhoneBurner\\Pinch\\Framework\\');
    }
}
