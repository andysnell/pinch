<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Exception;

use PhoneBurner\Pinch\Exception\UnableToWriteFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UnableToWriteFileTest extends TestCase
{
    #[Test]
    public function extendsRuntimeException(): void
    {
        $exception = UnableToWriteFile::atLocation('/tmp/output.txt');

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    #[Test]
    #[DataProvider('locationProvider')]
    public function atLocationCreatesCorrectMessage(string $location, string $reason, string $expected_message): void
    {
        $exception = UnableToWriteFile::atLocation($location, $reason);

        self::assertSame($expected_message, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    #[Test]
    public function atLocationWithPreviousException(): void
    {
        $location = '/var/log/application.log';
        $reason = 'Disk full';
        $previous = new \RuntimeException('Filesystem error');

        $exception = UnableToWriteFile::atLocation($location, $reason, $previous);

        self::assertSame('Unable to write file at location: /var/log/application.log. Disk full', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame(0, $exception->getCode());
    }

    #[Test]
    public function canBeThrown(): void
    {
        $this->expectException(UnableToWriteFile::class);
        $this->expectExceptionMessage('Unable to write file at location: /tmp/output.txt. Permission denied');

        throw UnableToWriteFile::atLocation('/tmp/output.txt', 'Permission denied');
    }

    #[Test]
    public function messageTrimsTrailingSpace(): void
    {
        // Test that trailing spaces are trimmed when reason is provided
        $exception1 = UnableToWriteFile::atLocation('/tmp/data.json', 'Disk full');
        self::assertSame('Unable to write file at location: /tmp/data.json. Disk full', $exception1->getMessage());

        // Test that trailing space is trimmed when no reason is provided
        $exception2 = UnableToWriteFile::atLocation('/tmp/data.json', '');
        self::assertSame('Unable to write file at location: /tmp/data.json.', $exception2->getMessage());

        // Test with only spaces as reason
        $exception3 = UnableToWriteFile::atLocation('/tmp/data.json', '   ');
        self::assertSame('Unable to write file at location: /tmp/data.json.', $exception3->getMessage());
    }

    #[Test]
    public function staticMethodReturnsCorrectType(): void
    {
        $exception = UnableToWriteFile::atLocation('/output.txt');

        self::assertInstanceOf(UnableToWriteFile::class, $exception);
    }

    public static function locationProvider(): \Iterator
    {
        yield 'with reason' => [
            '/var/log/error.log',
            'Permission denied',
            'Unable to write file at location: /var/log/error.log. Permission denied',
        ];

        yield 'without reason' => [
            '/tmp/output.csv',
            '',
            'Unable to write file at location: /tmp/output.csv.',
        ];

        yield 'with whitespace reason' => [
            '/home/user/report.pdf',
            '   ',
            'Unable to write file at location: /home/user/report.pdf.',
        ];

        yield 'relative path' => [
            'storage/uploads/file.txt',
            'Disk full',
            'Unable to write file at location: storage/uploads/file.txt. Disk full',
        ];

        yield 'windows path' => [
            'C:\\Users\\AppData\\settings.ini',
            'File locked by another process',
            'Unable to write file at location: C:\\Users\\AppData\\settings.ini. File locked by another process',
        ];

        yield 'file with multiple extensions' => [
            '/backups/database.sql.gz',
            'Insufficient space',
            'Unable to write file at location: /backups/database.sql.gz. Insufficient space',
        ];
    }
}
