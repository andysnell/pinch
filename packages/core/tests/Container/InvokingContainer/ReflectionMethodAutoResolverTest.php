<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Container\InvokingContainer;

use PhoneBurner\Pinch\Container\AutowiringContainer;
use PhoneBurner\Pinch\Container\Exception\UnableToAutoResolveParameter;
use PhoneBurner\Pinch\Container\InvokingContainer\ReflectionMethodAutoResolver;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideByParameterName;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideByParameterPosition;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideByParameterType;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\Pinch\Tests\Fixtures\MethodFixture;
use PhoneBurner\Pinch\Tests\Fixtures\StringWrapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ReflectionMethodAutoResolverTest extends TestCase
{
    private ReflectionMethodAutoResolver $resolver;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(AutowiringContainer::class);
        $this->resolver = new ReflectionMethodAutoResolver($this->container);
    }

    #[Test]
    public function resolvesParameterByPosition(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithParameters');
        $parameter = $method->getParameters()[0]; // First parameter: $first

        $override_value = 'position override';
        $override = new OverrideByParameterPosition(0, $override_value);
        $overrides = new OverrideCollection($override);

        $resolver = new ReflectionMethodAutoResolver($this->container, $overrides);

        self::assertSame($override_value, $resolver($parameter));
    }

    #[Test]
    public function resolvesParameterByName(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithParameters');
        $parameter = $method->getParameters()[1]; // Second parameter: $second

        $override_value = 'name override';
        $override = new OverrideByParameterName('second', $override_value);
        $overrides = new OverrideCollection($override);

        $resolver = new ReflectionMethodAutoResolver($this->container, $overrides);

        self::assertSame($override_value, $resolver($parameter));
    }

    #[Test]
    public function resolvesParameterByType(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithTypeHint');
        $parameter = $method->getParameters()[0];

        $mock = new StringWrapper('foo');
        $override = new OverrideByParameterType(StringWrapper::class, $mock);
        $overrides = new OverrideCollection($override);

        $resolver = new ReflectionMethodAutoResolver($this->container, $overrides);

        self::assertSame($mock, $resolver($parameter));
    }

    #[Test]
    public function resolvesParameterFromContainer(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithTypeHint');
        $parameter = $method->getParameters()[0]; // Parameter: LoggerInterface $logger

        $mock = new StringWrapper('foo');

        $container = $this->createMock(AutowiringContainer::class);
        $container->expects($this->once())
            ->method('has')
            ->with(StringWrapper::class, true)
            ->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with(StringWrapper::class)
            ->willReturn($mock);

        $resolver = new ReflectionMethodAutoResolver($container);

        self::assertSame($mock, $resolver($parameter));
    }

    #[Test]
    public function usesDefaultValueWhenNoTypeAndDefaultAvailable(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithDefaultValue');
        $parameter = $method->getParameters()[0]; // Parameter: $param = 'default'

        self::assertSame('default', ($this->resolver)($parameter));
    }

    #[Test]
    public function throwsWhenNoTypeAndNoDefaultAvailable(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithParameters');
        $parameter = $method->getParameters()[0]; // Parameter: $first

        $this->expectException(UnableToAutoResolveParameter::class);
        ($this->resolver)($parameter);
    }

    #[Test]
    public function prefersDefaultValueOverAutowiring(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithDefaultAndType');
        $parameter = $method->getParameters()[0]; // Parameter: LoggerInterface $logger = null

        // Container should not be called
        $container = $this->createMock(AutowiringContainer::class);
        $container->expects($this->once())
            ->method('has')
            ->with(StringWrapper::class, true)
            ->willReturn(false);
        $container->expects($this->never())
            ->method('get');

        $resolver = new ReflectionMethodAutoResolver($container);

        self::assertNull($resolver($parameter));
    }

    #[Test]
    public function fallsBackToContainerResolveWhenNoOtherOptions(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithTypeHint');
        $parameter = $method->getParameters()[0]; // Parameter: LoggerInterface $logger

        $logger = new StringWrapper('');

        $container = $this->createMock(AutowiringContainer::class);
        $container->expects($this->once())
            ->method('has')
            ->with(StringWrapper::class, true)
            ->willReturn(false);
        $container->expects($this->once())
            ->method('get')
            ->with(StringWrapper::class)
            ->willReturn($logger);

        $resolver = new ReflectionMethodAutoResolver($container);

        self::assertSame($logger, $resolver($parameter));
    }

    #[Test]
    public function handlesNonNamedType(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithUnionType');
        $parameter = $method->getParameters()[0]; // Parameter: string|int $param

        $this->expectException(UnableToAutoResolveParameter::class);
        ($this->resolver)($parameter);
    }

    #[Test]
    public function handlesBuiltinType(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithBuiltinType');
        $parameter = $method->getParameters()[0]; // Parameter: string $param

        $this->expectException(UnableToAutoResolveParameter::class);
        ($this->resolver)($parameter);
    }

    #[Test]
    public function handlesSelfType(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithSelfType');
        $parameter = $method->getParameters()[0]; // Parameter: self $param

        $this->expectException(UnableToAutoResolveParameter::class);
        ($this->resolver)($parameter);
    }

    #[Test]
    public function supportsNonServiceContainer(): void
    {
        $method = new \ReflectionMethod(MethodFixture::class, 'methodWithTypeHint');
        $parameter = $method->getParameters()[0]; // Parameter: LoggerInterface $logger

        $logger = new StringWrapper('foo');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with(StringWrapper::class)
            ->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with(StringWrapper::class)
            ->willReturn($logger);

        $resolver = new ReflectionMethodAutoResolver($container);

        self::assertSame($logger, $resolver($parameter));
    }
}
