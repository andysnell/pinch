<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\UnableToReadFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UnableToReadFileTest extends TestCase
{
    #[Test]
    public function extendsRuntimeException(): void
    {
        $exception = UnableToReadFile::atLocation('/tmp/test.txt');

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    #[Test]
    #[DataProvider('locationProvider')]
    public function atLocationCreatesCorrectMessage(string $location, string $reason, string $expected_message): void
    {
        $exception = UnableToReadFile::atLocation($location, $reason);

        self::assertSame($expected_message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function atLocationWithPreviousException(): void
    {
        $location = '/etc/config.conf';
        $reason = 'File not found';
        $previous = new \RuntimeException('Filesystem error');

        $exception = UnableToReadFile::atLocation($location, $reason, $previous);

        self::assertSame('Unable to read file at location: /etc/config.conf. File not found', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame(0, $exception->getCode());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(UnableToReadFile::class);
        $this->expectExceptionMessage('Unable to read file at location: /tmp/test.txt. Permission denied');

        throw UnableToReadFile::atLocation('/tmp/test.txt', 'Permission denied');
    }

    #[Test]
    public function messageTrimsTrailingSpace(): void
    {
        // Test that trailing spaces are trimmed when reason is provided
        $exception1 = UnableToReadFile::atLocation('/tmp/test.txt', 'File corrupted');
        self::assertSame('Unable to read file at location: /tmp/test.txt. File corrupted', $exception1->getMessage());

        // Test that trailing space is trimmed when no reason is provided
        $exception2 = UnableToReadFile::atLocation('/tmp/test.txt', '');
        self::assertSame('Unable to read file at location: /tmp/test.txt.', $exception2->getMessage());

        // Test with only spaces as reason
        $exception3 = UnableToReadFile::atLocation('/tmp/test.txt', '   ');
        self::assertSame('Unable to read file at location: /tmp/test.txt.', $exception3->getMessage());
    }

    #[Test]
    public function staticMethodReturnsCorrectType(): void
    {
        $exception = UnableToReadFile::atLocation('/test.txt');

        self::assertInstanceOf(UnableToReadFile::class, $exception);
    }

    public static function locationProvider(): \Iterator
    {
        yield 'with reason' => [
            '/var/log/app.log',
            'Permission denied',
            'Unable to read file at location: /var/log/app.log. Permission denied',
        ];

        yield 'without reason' => [
            '/tmp/cache.json',
            '',
            'Unable to read file at location: /tmp/cache.json.',
        ];

        yield 'with whitespace reason' => [
            '/home/user/document.pdf',
            '   ',
            'Unable to read file at location: /home/user/document.pdf.',
        ];

        yield 'relative path' => [
            'config/database.php',
            'File not found',
            'Unable to read file at location: config/database.php. File not found',
        ];

        yield 'windows path' => [
            'C:\\Windows\\System32\\config.txt',
            'Access denied',
            'Unable to read file at location: C:\\Windows\\System32\\config.txt. Access denied',
        ];

        yield 'file with extension' => [
            '/uploads/image.png',
            'File corrupted',
            'Unable to read file at location: /uploads/image.png. File corrupted',
        ];
    }
}
