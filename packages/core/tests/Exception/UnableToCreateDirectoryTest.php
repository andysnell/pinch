<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\UnableToCreateDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UnableToCreateDirectoryTest extends TestCase
{
    #[Test]
    public function extendsRuntimeException(): void
    {
        $exception = UnableToCreateDirectory::atLocation('/tmp/test');

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    #[Test]
    #[DataProvider('locationProvider')]
    public function atLocationCreatesCorrectMessage(string $location, string $reason, string $expected_message): void
    {
        $exception = UnableToCreateDirectory::atLocation($location, $reason);

        self::assertSame($expected_message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function atLocationWithPreviousException(): void
    {
        $location = '/var/log/app';
        $reason = 'Permission denied';
        $previous = new \RuntimeException('Filesystem error');

        $exception = UnableToCreateDirectory::atLocation($location, $reason, $previous);

        self::assertSame('Unable to create directory at location: /var/log/app. Permission denied', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame(0, $exception->getCode());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(UnableToCreateDirectory::class);
        $this->expectExceptionMessage('Unable to create directory at location: /tmp/test. Access denied');

        throw UnableToCreateDirectory::atLocation('/tmp/test', 'Access denied');
    }

    #[Test]
    public function messageTrimsTrailingSpace(): void
    {
        // Test that trailing spaces are trimmed when reason is provided
        $exception1 = UnableToCreateDirectory::atLocation('/tmp/test', 'Some reason');
        self::assertSame('Unable to create directory at location: /tmp/test. Some reason', $exception1->getMessage());

        // Test that trailing space is trimmed when no reason is provided
        $exception2 = UnableToCreateDirectory::atLocation('/tmp/test', '');
        self::assertSame('Unable to create directory at location: /tmp/test.', $exception2->getMessage());

        // Test with only spaces as reason
        $exception3 = UnableToCreateDirectory::atLocation('/tmp/test', '   ');
        self::assertSame('Unable to create directory at location: /tmp/test.', $exception3->getMessage());
    }

    #[Test]
    public function staticMethodReturnsCorrectType(): void
    {
        $exception = UnableToCreateDirectory::atLocation('/test');

        self::assertInstanceOf(UnableToCreateDirectory::class, $exception);
    }

    public static function locationProvider(): \Iterator
    {
        yield 'with reason' => [
            '/var/log/app',
            'Permission denied',
            'Unable to create directory at location: /var/log/app. Permission denied',
        ];

        yield 'without reason' => [
            '/tmp/cache',
            '',
            'Unable to create directory at location: /tmp/cache.',
        ];

        yield 'with whitespace reason' => [
            '/home/user/docs',
            '   ',
            'Unable to create directory at location: /home/user/docs.',
        ];

        yield 'relative path' => [
            'uploads/images',
            'Disk full',
            'Unable to create directory at location: uploads/images. Disk full',
        ];

        yield 'windows path' => [
            'C:\\Program Files\\App',
            'Administrator privileges required',
            'Unable to create directory at location: C:\\Program Files\\App. Administrator privileges required',
        ];
    }
}
