<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Trait;

use PhoneBurner\Pinch\Exception\NotInstantiable;
use PhoneBurner\Pinch\Iterator\Iter;
use PhoneBurner\Pinch\Tests\Trait\Fixtures\ChildClassUsingTrait;
use PhoneBurner\Pinch\Tests\Trait\Fixtures\TestClassUsingTrait;
use PhoneBurner\Pinch\Trait\HasNonInstantiableBehavior;
use PhoneBurner\Pinch\Type\Reflect;
use PhoneBurner\Pinch\Uuid\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HasNonInstantiableBehaviorTest extends TestCase
{
    #[Test]
    public function itPreventsClassInstantiation(): void
    {
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage('Class ' . TestClassUsingTrait::class . ' is not instantiable');

        new TestClassUsingTrait();
    }

    #[Test]
    public function exceptionContainsCorrectClassName(): void
    {
        try {
            new TestClassUsingTrait();
            self::fail('Expected NotInstantiable exception');
        } catch (NotInstantiable $e) {
            self::assertStringContainsString(TestClassUsingTrait::class, $e->getMessage());
            self::assertStringContainsString('is not instantiable', $e->getMessage());
        }
    }

    #[Test]
    public function exceptionContainsSelfReferenceNotTraitName(): void
    {
        try {
            new TestClassUsingTrait();
            self::fail('Expected NotInstantiable exception');
        } catch (NotInstantiable $e) {
            // Verify it uses self::class (the using class) not the trait name
            self::assertStringContainsString(TestClassUsingTrait::class, $e->getMessage());
            self::assertStringNotContainsString(HasNonInstantiableBehavior::class, $e->getMessage());
        }
    }

    #[Test]
    #[DataProvider('utilityClassProvider')]
    public function realUtilityClassesCannotBeInstantiated(string $utility_class): void
    {
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(\sprintf('Class %s is not instantiable', $utility_class));

        new $utility_class();
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function utilityClassProvider(): \Iterator
    {
        yield 'Uuid' => [Uuid::class];
        yield 'Iter' => [Iter::class];
        yield 'Reflect' => [Reflect::class];
    }

    #[Test]
    public function itWorksWithInheritance(): void
    {
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage('Class ' . ChildClassUsingTrait::class . ' is not instantiable');

        new ChildClassUsingTrait();
    }
}
