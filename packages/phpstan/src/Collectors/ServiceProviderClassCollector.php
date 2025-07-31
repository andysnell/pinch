<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Collectors;

use PhoneBurner\Pinch\Component\App\ServiceProvider;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * @implements Collector<InClassNode, array{class-string, int}>
 */
class ServiceProviderClassCollector implements Collector
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array|null
    {
        \assert($node instanceof InClassNode);

        $class = $node->getClassReflection();
        if ($class->implementsInterface(ServiceProvider::class)) {
            return [$class->getName(), $node->getStartLine()];
        }

        return null;
    }
}
